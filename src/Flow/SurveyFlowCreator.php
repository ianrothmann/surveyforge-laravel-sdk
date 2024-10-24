<?php

namespace Surveyforge\Surveyforge\Flow;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Surveyforge\Surveyforge\Utils\ArrayUtils;

class SurveyFlowCreator
{

    protected $definition;
    protected $flowDefinition;

    protected $answerObject;
    protected $sections;
    protected $conditions;
    protected $theme;
    protected $text;
    protected $warnings=[
        'conditions'=>[],
        'answers'=>[],
    ];
    protected $runningFlowIndex=0;
    protected $questionNumber=0;

    public function __construct(array $surveyDefinition)
    {
        $this->definition=collect($surveyDefinition);

        $this->answerObject=collect();
        $this->sections=collect();
        $this->conditions=collect();
        $this->theme=collect();
        $this->flowDefinition=$this->extractFlow();
        $this->validate();
    }

    public function get()
    {
        return collect([
            'survey' => $this->getTruncatedSurvey(),
            'sections' => $this->sections->toArray(),
            'flow' => $this->flowDefinition->toArray(),
            'conditions' => $this->conditions->toArray(),
            'answer_object' => $this->answerObject->toArray(),
            'warnings' => $this->warnings,
            'theme' => $this->theme->toArray(),
            'text' => $this->text->toArray(),
            'options' => $this->getOptions()->toArray(),
        ]);
    }

    protected function getOptions()
    {
        return collect($this->definition->get('options'));
    }

    protected function getTruncatedSurvey()
    {
        return $this->definition->only(['title','theme','languages']);
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function getFlow()
    {
        return $this->flowDefinition;
    }

    protected function extractFlow()
    {
        $this->runningFlowIndex=0;
        $flow=collect();
        $sections=collect($this->definition->get('sections'))
            ->map(function($section, $idx){
                return $this->extractSection(collect($section),$idx);
            })
            ->flatten(1)
            ->toArray();

        $flow=$flow->concat($sections)->filter();

        $flow=$this->extractConditions($flow);

        $this->theme=collect($this->definition->get('theme'));

        $this->text = $this->extractText();
        return $flow;
    }

    protected function extractText()
    {
        if(!$this->definition['text'] ?? false){
            return collect();
        }

        return collect($this->definition['text']['texts'] ?? [])
            ->map(function($txt){
                 return collect($txt['text'] ?? [])->toArray();
            });
    }

    protected function extractSection($section, $sectionId)
    {
        $startIndex=$this->runningFlowIndex;
        $conditionId=$this->registerCondition($section->get('condition'));

        $flow=collect($section->get('instructions'))->map(function($content) use($sectionId,$conditionId){
            $this->runningFlowIndex++;
            $content['conditions']=$this->combineConditions([$conditionId]);
            $content['section_id']=$sectionId;
            return $content;
        });

        $questions=collect($section->get('questions'))
            ->map(function($question, $idx) use($sectionId,$conditionId){
                $this->runningFlowIndex++;
                return $this->extractQuestion(collect($question),$sectionId,$conditionId);
            })
            ->flatten(1)
            ->toArray();

        $this->registerSection($sectionId,$section->only('title'),$startIndex,$this->runningFlowIndex);
        $flow=$flow->concat($questions);

        return $flow;
    }

    protected function registerSection($order, $props, $startIndex, $endIndex)
    {
        $props['id']=$order;
        $props['start_index']=$startIndex;
        $props['end_index']=$endIndex;
        $this->sections->put($order,$props);
        return $order;
    }

    protected function extractQuestion(Collection $question, $sectionId, $parentConditionId=null)
    {
        $conditionId=$this->registerCondition($question->get('condition'));
        $flow=collect($question->get('instructions'))->map(function($content) use($conditionId,$parentConditionId,$sectionId){
            $content['conditions']=$this->combineConditions([$parentConditionId,$conditionId]);
            $content['section_id']=$sectionId;
            return $content;
        });
        $question['question_number']=++$this->questionNumber;
        $this->registerAnswerObject($question->get('answer_object'));
        $question->forget('answer_object');
        $question->forget('instructions');
        $question['section_id']=$sectionId;
        $question['conditions']=$this->combineConditions([$parentConditionId,$conditionId]);
        unset($question['condition']);

        $question=$this->addAnswerReferencesToQuestion($question->toArray());

        $flow->add($question);
        return $flow;
    }

    protected function addAnswerReferencesToQuestion(array $question)
    {
        $questionId=$question['question_id'] ?? null;
        if($question['answer']['fields'] ?? null){
            $question['answer']['fields']=collect($question['answer']['fields'])->map(function($field) use($questionId){
                if($field['field']['field_id'] ?? null){
                    $fieldRef=$questionId.'.'.$field['field']['field_id'];
                    $field['field']['answer_ref']=$fieldRef;
                    /*
                    //Now map option refs
                    if(($field['field']['multiple'] ?? false) && ($field['field']['options'] ?? null)){
                        $field['field']['options']=collect($field['field']['options'])->map(function($option) use($fieldRef){
                            if($option['option_id'] ?? null){
                                $option['answer_ref']=$fieldRef.'.'.$option['option_id'];
                            }
                            return $option;
                        })->toArray();
                    }
                    //TODO This code was to build one answer per option. But this has been reverted, keeping the code for future expansion.
                    */
                }
                return $field;
            })->toArray();
        }

        return $question;
    }

    protected function registerAnswerObject($answerObject)
    {
        collect($answerObject)->each(function($fieldObject, $questionId){
            $this->answerObject->put($questionId,$fieldObject);
        });
    }

    protected function combineConditions($conditionArray)
    {
        return collect($conditionArray)->filter()->toArray();
    }

    protected function extractConditions($flow)
    {
        $flow=$flow->toArray();
        ArrayUtils::updateNodeRecursive($flow, function(&$item){
            $temp=collect($item);
            if($temp->get('definition_type')==='condition'){
                $item=$this->registerCondition($item);
            }
        });

        return collect($flow);
    }

    protected function registerCondition($condition)
    {
        if(!$condition){
            return null;
        }
        $id=Uuid::uuid4()->toString();
        $this->conditions->put($id,$condition);
        return $id;
    }

    protected function validate()
    {
        $this->checkConditionReferences();
        $this->checkAnswerReferences();
    }

    protected function checkConditionReferences()
    {
        $answers=$this->answerObject->toArray();
        $this->conditions->each(function($condition, $conditionId) use($answers){
            collect($condition['columns'])->each(function($column) use($answers,$conditionId){
                if(!Arr::has($answers,$column)){
                    $this->warnings['conditions'][]=[
                        'condition_id'=>$conditionId,
                        'column'=>$column,
                        'message' => $column.' not found'
                    ];
                }
            });
        });
    }

    protected function checkAnswerReferences()
    {
        $flow=$this->flowDefinition;
        $answers=$this->answerObject->toArray();
        array_walk_recursive($flow, function($answerRef, $key) use($answers){
            if($key==='answer_ref'){
                if(!Arr::has($answers,$answerRef)){
                    $this->warnings['answers'][]=[
                        'answer_ref'=>$answerRef,
                        'message' => $answerRef.' not found'
                    ];
                }
            }
        });
    }
}

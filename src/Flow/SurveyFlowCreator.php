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
    protected $warnings=[
        'conditions'=>[],
        'answers'=>[],
    ];

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
            'sections' => $this->sections->toArray(),
            'flow' => $this->flowDefinition->toArray(),
            'conditions' => $this->conditions->toArray(),
            'answer_object' => $this->answerObject->toArray(),
            'warnings' => $this->warnings,
            'theme' => $this->theme->toArray()
        ]);
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

        return $flow;
    }

    protected function extractSection($section, $sectionId)
    {
        $this->registerSection($sectionId,$section->only('title'));
        $conditionId=$this->registerCondition($section->get('condition'));
        $flow=collect($section->get('instructions'))->map(function($content) use($sectionId,$conditionId){
            $content['flow_id']=Str::orderedUuid()->toString();
            $content['conditions']=$this->combineConditions([$conditionId]);
            $content['section_id']=$sectionId;
            return $content;
        });

        $questions=collect($section->get('questions'))
            ->map(function($question, $idx) use($sectionId,$conditionId){
                return $this->extractQuestion(collect($question),$sectionId,$conditionId);
            })
            ->flatten(1)
            ->toArray();

        $flow=$flow->concat($questions);

        return $flow;
    }

    protected function registerSection($order, $props)
    {
        $props['id']=$order;
        $this->sections->put($order,$props);
        return $order;
    }

    protected function extractQuestion(Collection $question, $sectionId, $parentConditionId=null)
    {
        $conditionId=$this->registerCondition($question->get('condition'));
        $flow=collect($question->get('instructions'))->map(function($content) use($conditionId,$parentConditionId){
            $content['flow_id']=Str::orderedUuid()->toString();
            $content['conditions']=$this->combineConditions([$parentConditionId,$conditionId]);
            return $content;
        });
        $question['flow_id']=Str::orderedUuid()->toString();
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
                    //Now map option refs
                    if(($field['field']['multiple'] ?? false) && ($field['field']['options'] ?? null)){
                        $field['field']['options']=collect($field['field']['options'])->map(function($option) use($fieldRef){
                            if($option['option_id'] ?? null){
                                $option['answer_ref']=$fieldRef.'.'.$option['option_id'];
                            }
                            return $option;
                        })->toArray();
                    }
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

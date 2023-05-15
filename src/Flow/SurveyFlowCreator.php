<?php

namespace Surveyforge\Surveyforge\Flow;

use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

class SurveyFlowCreator
{

    protected $surveyDefinition;
    protected $flowDefinition;

    protected $answerObject;
    protected $sections;
    protected $conditions;

    public function __construct(array $surveyDefinition)
    {
        $this->definition=collect($surveyDefinition);

        $this->answerObject=collect();
        $this->sections=collect();
        $this->conditions=collect();
        $this->flowDefinition=$this->extractFlow();
        dd($this->sections, $this->flowDefinition, $this->conditions, $this->answerObject);
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

        return $flow;
    }

    protected function extractSection($section, $sectionId)
    {
        $this->registerSection($sectionId,$section->only('title'));
        $conditionId=$this->registerCondition($section->get('condition'));
        $flow=collect($section->get('instructions'))->map(function($content) use($sectionId,$conditionId){
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
            $content['conditions']=$this->combineConditions([$parentConditionId,$conditionId]);
            return $content;
        });
        $this->registerAnswerObject($question->get('answer_object'));
        $question->forget('answer_object');
        $question->forget('instructions');
        $question['section_id']=$sectionId;
        $question['conditions']=$this->combineConditions([$parentConditionId,$conditionId]);
        unset($question['condition']);
        $flow->add($question);
        return $flow;
    }

    protected function registerAnswerObject($answerObject)
    {
        $this->answerObject->add($answerObject);
    }

    protected function combineConditions($conditionArray)
    {
        return collect($conditionArray)->filter()->toArray();
    }

    protected function extractConditions($flow)
    {
        $flow=$flow->toArray();
        self::updateNodeRecursive($flow, function(&$item){
            $temp=collect($item);
            if($temp->get('definition_type')==='condition'){
                $item=$this->registerCondition($item);
            }
        });

        return $flow;
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

    public static function updateNodeRecursive(&$arr, callable $callback)
    {
            foreach ($arr as $key => &$value) {
                if (is_array($value) || $value instanceof Collection) {
                    $callback($value, $key);
                    if (is_array($value) || $value instanceof Collection) {
                        self::updateNodeRecursive($value, $callback);
                    }
                }
            }
            unset($value);
    }
}

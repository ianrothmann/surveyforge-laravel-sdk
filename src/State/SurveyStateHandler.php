<?php

namespace Surveyforge\Surveyforge\State;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Surveyforge\Surveyforge\Definitions\Survey;
use Surveyforge\Surveyforge\Expression\Expression;
use Surveyforge\Surveyforge\Flow\SurveyFlowCreator;
use Surveyforge\Surveyforge\Utils\ArrayUtils;

class SurveyStateHandler
{
    protected $flow;
    protected array $answerObject;
    protected $conditions;
    protected $sections;
    protected $parsedConditions;
    protected $currentIndex=0;

    public function __construct($flowDefinition)
    {
        $flowDefinition=collect($flowDefinition);
        $this->answerObject=$flowDefinition->get('answer_object');
        $this->sections=collect($flowDefinition->get('sections'));
        $this->flow=collect($flowDefinition->get('flow'));
        $this->conditions=collect($flowDefinition->get('conditions'));
    }

    public static function fromSurveyFlowObject($flow)
    {
        return new static($flow);
    }

    public static function fromSurveyFlowCreator(SurveyFlowCreator $flowCreator)
    {
        return new static($flowCreator->get());
    }

    public static function fromSurveyDefinitionObject($definition)
    {
        $flowCreator=new SurveyFlowCreator($definition);
        return self::fromSurveyFlowCreator($flowCreator);
    }

    public static function fromSurvey(Survey $survey)
    {
        return self::fromSurveyDefinitionObject($survey->build());
    }

    public function setAnswers(array $answers)
    {
        $this->answerObject=$answers;
        return $this;
    }

    public function setAnswer($answerRef, $answer)
    {
         Arr::set($this->answerObject,$answerRef,$answer);
         return $this;
    }

    public function setCurrentIndex($index)
    {
        $this->currentIndex=$index;
        return $this;
    }

    public function getCurrentFlowItem()
    {
        return $this->flow->get($this->currentIndex);
    }

    public function next()
    {
        $this->parseConditions();
        $flow=$this->getConditionalFlow();
        $flowMap=$this->getConditionedFlowMapByIndex($flow);

        for($i=$this->currentIndex+1;$i<$flowMap->count();$i++){
            if($flowMap->get($i)){
                $this->currentIndex=$i;
                return true;
            }
        }

        return false;
    }

    protected function getConditionedFlowMapByIndex($conditionalFlow)
    {
        $newFlowOrder=$conditionalFlow->pluck('flow_id');
        return $this->flow->mapWithKeys(function($flowItem, $index) use ($newFlowOrder){
            return [$index=>!!$newFlowOrder->contains($flowItem['flow_id'])];
        });
    }


    protected function getIndexForFlowId($flowOrder, $flowId)
    {
        return $this->flowOrder->filter(function ($id) use ($flowId) {
            return $id==$flowId;
        })->keys()->first();
    }

    protected function getConditionalFlow()
    {
        $flow=$this->flow->toArray();
        ArrayUtils::removeNodesRecursive($flow,function ($node) {
            if($node['condition'] ?? null){
                return !$this->parsedConditions->get($node['condition']);
            }elseif ($node['conditions'] ?? []){
                $tempNode=collect($node['conditions']);
                return $tempNode->count() == 0 || !$tempNode->every(function($condition){
                    return $this->parsedConditions->get($condition);
                });
            }

            return false;
        });
        return collect($flow);
    }

    protected function parseConditions()
    {
        $this->parsedConditions=$this->conditions->map(function($condition){
            return $this->parseCondition($condition);
        });
    }

    protected function parseCondition($condition)
    {
        $syntax=$this->replaceDotNotationWithUnderscores($condition['syntax']);
        $answers=collect($condition['columns'])->mapWithKeys(function($column){
            $columnKey=Str::replace('.','_',$column);
            return [
                $columnKey => Arr::get($this->answerObject,$column)
            ];
        });


        foreach ($answers->keys() as $column){
            $syntax=str_replace('`'.$column.'`',$column,$syntax);
        }

        $exp=new Expression($syntax);
        $exp->withVariables($answers);
        return $exp->evaluate();
    }

    protected function replaceDotNotationWithUnderscores($input)
    {
        return preg_replace_callback(
            '/`(.+?)`/',
            function ($matches) {
                return str_replace('.', '_', $matches[0]);
            },
            $input
        );
    }


}

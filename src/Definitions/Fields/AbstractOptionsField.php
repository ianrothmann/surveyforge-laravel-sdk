<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Condition\Condition;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\HasMultipleAnswers;

abstract class AbstractOptionsField extends AbstractField
{

    protected $fieldId;
    protected $options;

    public function __construct($fieldId)
    {
        parent::__construct($fieldId);
        $this->options=collect();
    }

    protected function createOption($name, $optionId=null, $description=null, Condition $condition=null)
    {
        if($optionId===null){
            $optionId=($this->options->keys()->max ?? 0) + 1;
        }

        $def=[
            'option_id'=>$optionId,
            'name'=> $this->renderText($name),
            'order'=>$this->options->count()+1,
            'condition'=>$condition,
        ];

        if($description!==null){
            $def['description']=$this->renderText($description);
        }

        $this->options[]=$def;
    }

    protected function buildAnswerObject()
    {
        /*
        if($this instanceof HasMultipleAnswers){
            return $this->options->keyBy('option_id')->map(function($option){
                return null;
            })->toArray();
        }
        //TODO This code was to build one answer per option. But this has been reverted, keeping the code for future expansion.
        */

        return null;

    }

    protected function createCondition($callable, $runInverse=false)
    {
        if(!$callable){
            return null;
        }
        $condition=new Condition($runInverse);
        $callable($condition);
        return $condition;
    }

    public function toArray()
    {
        $definition=parent::toArray();
        $definition['multiple'] = $this instanceof HasMultipleAnswers;
        $definition['options']=$this->options
            ->map(function($option){
                if($option['condition']){
                    $option['condition']=$option['condition']->build();
                }
                return $option;
            })
            ->toArray();
        return $definition;
    }
}

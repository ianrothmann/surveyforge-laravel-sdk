<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;

abstract class AbstractOptionsField extends AbstractBuilder
{

    protected $fieldId;
    protected $options;

    public function __construct($fieldId)
    {
        $this->fieldId=$fieldId;
        $this->options=collect();
    }

    protected function createOption($name, $optionId=null, $description=null)
    {
        if($optionId===null){
            $optionId=($this->options->keys()->max ?? 0) + 1;
        }

        $def=[
            'option_id'=>$optionId,
            'name'=> $name,
            'order'=>$this->options->count()+1,
        ];

        if($description!==null){
            $def['description']=$description;
        }

        $this->options[]=$def;
    }

    public function toArray()
    {
        // TODO: Implement toArray() method.
    }
}

<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Illuminate\Support\Str;
use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\CanBeUsedOnForms;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

abstract class AbstractField extends AbstractBuilder
{
    protected $fieldId;
    protected string $type;
    protected bool $optional=false;
    protected ?string $name;
    protected $definitionType=DefinitionType::FIELD;
    protected string $validator='';

    public function __construct($fieldId)
    {
        parent::__construct();
        $this->fieldId=$fieldId;
    }

    public function setOptional($optional=true)
    {
        $this->optional=$optional;
        return $this;
    }

    public function validateWith($validator)
    {
        $this->validator=$validator;
        return $this;
    }

    protected function buildAnswerObject()
    {
        return null;
    }

    public function getValidator()
    {
        if($this->validator && !$this->optional && Str::contains($this->validator,'required')){
            return $this->validator.'|required';
        }elseif(!$this->validator && !$this->optional && Str::contains($this->validator,'required')){
            return $this->validator='required';
        }else{
            return $this->validator;
        }
    }

    public function toArray()
    {
        $definition=[
            'field_id'=>$this->fieldId,
            'definition_type'=>$this->definitionType,
            'field_type'=>$this->type,
            'validator'=>$this->getValidator(),
            'answer_object'=>$this->buildAnswerObject(),
        ];

        if($this instanceof CanBeUsedOnForms){
            $definition['name']=$this->name;
        }

        return $definition;
    }
}

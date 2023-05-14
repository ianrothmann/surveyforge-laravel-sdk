<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

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

    public function toArray()
    {
        $definition=[
            'field_id'=>$this->fieldId,
            'definition_type'=>$this->definitionType,
            'field_type'=>$this->type
        ];

        if($this instanceof CanBeUsedOnForms){
            $definition['name']=$this->name;
        }

        return $definition;
    }
}

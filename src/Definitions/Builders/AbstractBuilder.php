<?php

namespace Surveyforge\Surveyforge\Definitions\Builders;

use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

abstract class AbstractBuilder
{
    protected $definitionType=null;

    public function __construct()
    {

    }

    abstract public function toArray();

    public function getDefinitionType()
    {
        return $this->definitionType;
    }

    public function build()
    {
        $definition=$this->toArray();

        if(!$this->getDefinitionType()){
            throw new \Exception('Definition type is not set for '.get_class($this).'.');
        }

        $definition['definition_type']=$this->getDefinitionType();

        return $definition;
    }
}

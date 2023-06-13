<?php

namespace Surveyforge\Surveyforge\Definitions\Builders;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;
use Surveyforge\Surveyforge\Definitions\Text\Text;

abstract class AbstractBuilder
{
    protected $definitionType=null;
    protected $texts=[];

    public function __construct()
    {

    }

    abstract public function toArray();

    public function getDefinitionType()
    {
        return $this->definitionType;
    }

    protected function renderText($stringOrTextInstance): ?string
    {
        if($stringOrTextInstance instanceof Text){
            return $stringOrTextInstance->render();
        }else{
            return $stringOrTextInstance;
        }
    }

    public function build()
    {
        $definition=$this->toArray();

        if(!$this->getDefinitionType()){
            throw new \Exception('Definition type is not set for '.get_class($this).'.');
        }

        $definition['definition_id']=Str::orderedUuid()->toString();
        $definition['definition_type']=$this->getDefinitionType();

        return $definition;
    }
}

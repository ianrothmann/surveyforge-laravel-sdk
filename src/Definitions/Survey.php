<?php

namespace Surveyforge\Surveyforge\Definitions;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

class Survey extends AbstractBuilder
{
    protected $definitionType=DefinitionType::SURVEY;

    public function toArray()
    {
        // TODO: Implement toArray() method.
    }
}

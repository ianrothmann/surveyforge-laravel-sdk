<?php

namespace Surveyforge\Surveyforge\Definitions;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

class Section extends AbstractBuilder
{
    protected $definitionType=DefinitionType::SECTION;

    public function toArray()
    {
        // TODO: Implement toArray() method.
    }
}

<?php

namespace Surveyforge\Surveyforge\Definitions\Content;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

abstract class AbstractContent extends AbstractBuilder
{
    const GRID = 'grid';
    const HTML = 'html';
    const QUESTION_BLOCK='question_block';

    protected $definitionType=DefinitionType::CONTENT;
    protected string $type;

    public function toArray()
    {
        return [
            'type'=>$this->type
        ];
    }

}

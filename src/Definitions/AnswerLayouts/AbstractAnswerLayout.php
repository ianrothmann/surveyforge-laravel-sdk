<?php

namespace Surveyforge\Surveyforge\Definitions\AnswerLayouts;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Fields\AbstractField;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

abstract class AbstractAnswerLayout extends AbstractBuilder
{
    const SINGLE_ANSWER_LAYOUT = 'single_answer';
    const FORM = 'form';

    protected $definitionType=DefinitionType::ANSWER;
    protected $fields;

    public function __construct()
    {
        parent::__construct();
        $this->fields=collect();
    }

    public function toArray(): array
    {
        return [
            'type'=>$this->type,
            'fields'=>$this->fields->map(function(AbstractField $field){
                return $field->build();
            })->values()
        ];
    }
}

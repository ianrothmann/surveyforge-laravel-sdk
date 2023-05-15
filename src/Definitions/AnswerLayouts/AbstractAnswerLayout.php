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
        $builtFields=$this->fields->map(function($field){
            $field['field']=$field['field']->build();
            return $field;
         });
        return [
            'type'=>$this->type,
            'answer_object'=>$builtFields
                ->map(fn($f)=>$f['field'])
                ->keyBy('field_id')->map(function($field){
                return $field['answer_object'];
            })->toArray(),
            'fields'=>$builtFields
                ->map(function($field){
                    unset($field['field']['answer_object']);
                    return $field;
                })
                ->values()->toArray(),
        ];
    }
}

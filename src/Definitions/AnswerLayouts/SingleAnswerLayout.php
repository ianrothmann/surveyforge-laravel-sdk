<?php

namespace Surveyforge\Surveyforge\Definitions\AnswerLayouts;

use Surveyforge\Surveyforge\Definitions\Fields\AbstractField;

class SingleAnswerLayout extends AbstractAnswerLayout
{
    protected string $type = self::SINGLE_ANSWER_LAYOUT;

    public function withField(AbstractField $field)
    {
        $this->fields[] = [
            'field'=>$field
        ];

        return $this;
    }
}

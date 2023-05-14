<?php

namespace Surveyforge\Surveyforge\Definitions\AnswerLayouts;

use Surveyforge\Surveyforge\Definitions\Fields\AbstractField;

class FormAnswerLayout extends AbstractAnswerLayout
{
    protected string $type = self::FORM;

    protected int $gridColumns=2;

    public function numberOfGridColumns($numberOfColumns)
    {
        $this->gridColumns=$numberOfColumns;
        return $this;
    }

    public function withField(AbstractField $field, $colSpan=1, $rowSpan=1)
    {
        $this->fields[] = [
            'field'=>$field,
            'colSpan'=>$colSpan,
            'rowSpan'=>$rowSpan
        ];
        return $this;
    }

    public function toArray():array
    {
        $definition=parent::toArray();
        $definition['grid_columns']=$this->gridColumns;
        return $definition;
    }

}

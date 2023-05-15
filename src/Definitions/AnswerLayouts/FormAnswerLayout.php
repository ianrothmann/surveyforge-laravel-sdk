<?php

namespace Surveyforge\Surveyforge\Definitions\AnswerLayouts;

use Surveyforge\Surveyforge\Definitions\Fields\AbstractField;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\CanBeUsedOnForms;

class FormAnswerLayout extends AbstractAnswerLayout
{
    protected string $type = self::FORM;

    protected int $gridColumns=2;

    public function numberOfGridColumns($numberOfColumns)
    {
        $this->gridColumns=$numberOfColumns;
        return $this;
    }

    public function addField(AbstractField $field, $colSpan=1, $rowSpan=1)
    {
        if(!$field instanceof CanBeUsedOnForms){
            throw new \Exception(get_class($field).' cannot be used on forms.');
        }
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

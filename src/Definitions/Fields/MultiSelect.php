<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class MultiSelect extends AbstractOptionsField
{
    protected string $type = FieldType::MULTI_SELECT;
    protected string $name;

    public function __construct($fieldId, $name)
    {
        parent::__construct($fieldId);
        $this->name = $name;
    }

    public function addOption($name, $optionId = null, $description=null)
    {
        $this->createOption($name, $optionId, $description);
        return $this;
    }
}

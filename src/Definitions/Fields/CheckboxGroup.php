<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\CanBeUsedOnForms;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class CheckboxGroup extends AbstractOptionsField implements CanBeUsedOnForms
{
    protected string $type = FieldType::CHECKBOX_GROUP;

    protected ?string $name;

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

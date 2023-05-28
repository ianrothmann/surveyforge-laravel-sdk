<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\CanBeUsedOnForms;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class Dropdown extends AbstractOptionsField implements CanBeUsedOnForms
{
    protected string $type = FieldType::DROPDOWN;
    protected ?string $name;

    public function __construct($fieldId, $name)
    {
        parent::__construct($fieldId);
        $this->name = $name;
    }

    public function addOption($name, $optionId = null, $description=null, $showWhen = null)
    {
        $this->createOption($name, $optionId, $description, $this->createCondition($showWhen));
        return $this;
    }

}

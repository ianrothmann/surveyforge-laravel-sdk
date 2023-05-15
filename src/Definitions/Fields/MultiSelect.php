<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\CanBeUsedOnForms;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\HasMultipleAnswers;

class MultiSelect extends AbstractOptionsField implements CanBeUsedOnForms, HasMultipleAnswers
{
    protected string $type = FieldType::MULTI_SELECT;
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

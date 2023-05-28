<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Condition\Condition;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class OptionsField extends AbstractOptionsField
{
    protected string $type = FieldType::OPTIONS;

    public function addOption($name, $optionId = null, $description=null, $showWhen=null)
    {
        $this->createOption($name, $optionId, $description, $this->createCondition($showWhen));
        return $this;
    }
}

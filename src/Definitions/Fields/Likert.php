<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class Likert extends AbstractOptionsField
{
    protected string $type = FieldType::LIKERT;

    public function addOption($name, $optionId = null, $description=null, $showWhen = null)
    {
        $this->createOption($name, $optionId, $description, $this->createCondition($showWhen));
        return $this;
    }
}

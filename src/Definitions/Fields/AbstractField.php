<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;

abstract class AbstractField extends AbstractBuilder
{

    protected $fieldId;

    public function __construct($fieldId)
    {
        $this->fieldId=$fieldId;
    }

    public function toArray()
    {
        // TODO: Implement toArray() method.
    }
}

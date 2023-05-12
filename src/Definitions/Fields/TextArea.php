<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\CanBeUsedOnForms;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class TextArea extends AbstractTextField implements CanBeUsedOnForms
{
    protected string $type = FieldType::TEXT_AREA;
}

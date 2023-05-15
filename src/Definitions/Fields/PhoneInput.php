<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\CanBeUsedOnForms;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class PhoneInput extends TextInput implements CanBeUsedOnForms
{
    protected string $type = FieldType::PHONE;


}

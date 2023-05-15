<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\CanBeUsedOnForms;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class CountryInput extends TextInput implements CanBeUsedOnForms
{
    protected string $type = FieldType::COUNTRY;

    public function __construct($fieldId='country', $name='Country')
    {
        parent::__construct($fieldId, $name);
    }


}

<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Fields\Text;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Fields\TextInput;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;

class FirstNameField extends AbstractPredefinedBuilder
{
    public static function get($fieldId='first_name'): TextInput
    {
        return (new TextInput($fieldId,'First name'));
    }
}

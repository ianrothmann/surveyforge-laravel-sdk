<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Fields\Text;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Fields\TextInput;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;

class LastNameField extends AbstractPredefinedBuilder
{
    public static function get($fieldId='last_name'): TextInput
    {
        return (new TextInput($fieldId,'Last name'))
            ->validateWith('required|min:2|max:255');
    }
}

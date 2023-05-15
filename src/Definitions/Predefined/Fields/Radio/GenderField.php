<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Fields\Radio;

use Surveyforge\Surveyforge\Definitions\Fields\RadioGroup;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;

class GenderField extends AbstractPredefinedBuilder
{
    public static function get($fieldId='gender'): RadioGroup
    {
        return (new RadioGroup($fieldId,'Gender'))
                ->addOption('Male',0)
                ->addOption('Female',1)
                ->addOption('Other',2);
    }
}

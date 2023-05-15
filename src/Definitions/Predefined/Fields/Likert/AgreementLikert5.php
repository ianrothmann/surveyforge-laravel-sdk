<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Fields\Likert;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Fields\Likert;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;

class AgreementLikert5 extends AbstractPredefinedBuilder
{
    public static function get($fieldId='agree'): Likert
    {
        return (new Likert($fieldId))
                ->addOption('Strongly Disagree',1)
                ->addOption('Disagree',2)
                ->addOption('Neutral',3)
                ->addOption('Agree',4)
                ->addOption('Strongly Agree',5);
    }
}

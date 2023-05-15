<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Fields\Likert;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Fields\Likert;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;

class NeverAlwaysLikert5 extends AbstractPredefinedBuilder
{
    public static function get($fieldId='never_always'): Likert
    {
        return (new Likert($fieldId))
            ->addOption('Never',1)
            ->addOption('Seldom',2)
            ->addOption('Sometimes',3)
            ->addOption('Often',4)
            ->addOption('Always',5);
    }
}

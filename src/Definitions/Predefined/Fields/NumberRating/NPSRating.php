<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Fields\NumberRating;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Fields\NumberRating;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;

class NPSRating extends AbstractPredefinedBuilder
{
    public static function get($fieldId='nps'): NumberRating
    {
        return (new NumberRating($fieldId))
            ->withRange(1, 10, 'Not at all', 'Definitely');
    }
}

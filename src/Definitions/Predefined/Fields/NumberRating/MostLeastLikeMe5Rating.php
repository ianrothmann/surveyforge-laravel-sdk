<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Fields\NumberRating;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Fields\NumberRating;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;

class MostLeastLikeMe5Rating extends AbstractPredefinedBuilder
{
    public static function get($fieldId='agree_disagree'): NumberRating
    {
        return (new NumberRating($fieldId))
            ->withRange(1,5,'Strongly Disagree','Strongly Agree');
    }
}

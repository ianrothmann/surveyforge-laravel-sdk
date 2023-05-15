<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Questions;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\NumberRating\NPSRating;
use Surveyforge\Surveyforge\Definitions\Questions\VerticalQuestion;

class NPSQuestion extends AbstractPredefinedBuilder
{
    public static function get($questionId='nps'): AbstractBuilder
    {
        return (new VerticalQuestion($questionId))
            ->withQuestionText('How likely are you to recommend our products and services to your friends and family?')
            ->withAnswerField(NPSRating::get($questionId));
    }
}

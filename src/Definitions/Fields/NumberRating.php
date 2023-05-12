<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class NumberRating extends AbstractField
{

    protected string $type = FieldType::NUMBER_RATING;

    protected int $min;
    protected int $max;
    protected string $minLabel;
    protected string $maxLabel;

    public function withRange($min, $max, $minLabel=null, $maxLabel=null)
    {
        $this->min = $min;
        $this->max = $max;
        $this->minLabel = $minLabel;
        $this->maxLabel = $maxLabel;
        return $this;
    }
}
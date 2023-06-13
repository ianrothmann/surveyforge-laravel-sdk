<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class NumberRating extends AbstractField
{

    protected string $type = FieldType::NUMBER_RATING;

    protected int $min;
    protected int $max;
    protected $minLabel;
    protected $maxLabel;

    public function withRange($min, $max, $minLabel=null, $maxLabel=null)
    {
        $this->min = $min;
        $this->max = $max;
        $this->minLabel = $this->renderText($minLabel);
        $this->maxLabel = $this->renderText($maxLabel);
        return $this;
    }

    public function toArray()
    {
        $definition=parent::toArray();
        $definition['min']=$this->min;
        $definition['min_label']=$this->minLabel;
        $definition['max']=$this->max;
        $definition['max_label']=$this->maxLabel;
        return $definition;
    }
}

<?php

namespace Surveyforge\Surveyforge\Definitions\Questions;

use Surveyforge\Surveyforge\Definitions\Content\AbstractContent;

class StepQuestion extends AbstractQuestion
{
    protected string $type=self::STEP;
    protected $steps;

    public function __construct(string $questionId)
    {
        parent::__construct($questionId);
        $this->steps=collect();
    }

    public function toArray()
    {
        $definition=parent::toArray();
        $definition['steps']=$this->steps->map(function(AbstractContent $content){
            $content->build();
        });
        return $definition;
    }
}

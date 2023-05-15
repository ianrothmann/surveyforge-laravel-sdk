<?php

namespace Surveyforge\Surveyforge\Definitions\Condition;

trait ConditionHandlerTrait
{
    protected ?Condition $condition=null;

    public function showWhen($callable)
    {
        $condition=new Condition();
        $callable($condition);
        $this->condition=$condition;
        return $this;
    }

    public function doNotShowWhen($callable)
    {
        $condition=new Condition(true);
        $callable($condition);
        $this->condition=$condition;
        return $this;
    }
}

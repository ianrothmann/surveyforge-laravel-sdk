<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

abstract class AbstractTextField extends AbstractField
{

    protected $name;
    protected string $hint='';
    protected string $placeholder='';
    protected $maxChars;
    protected $minChars;

    public function __construct($fieldId, $name)
    {
        parent::__construct($fieldId);
        $this->name = $this->renderText($name);
    }

    public function toArray()
    {
        $definition=parent::toArray();
        $definition['name']=$this->name;
        $definition['hint']=$this->hint;
        $definition['placeholder']=$this->placeholder;
        $definition['max_chars']=$this->maxChars;
        $definition['min_chars']=$this->minChars;

        return $definition;
    }

    public function withHint($hint)
    {
        $this->hint=$this->renderText($hint);
        return $this;
    }

    public function setMinimumLength($minChars)
    {
        $this->minChars=$minChars;
        return $this;
    }

    public function setMaximumLength($maxChars)
    {
        $this->maxChars=$maxChars;
        return $this;
    }


}

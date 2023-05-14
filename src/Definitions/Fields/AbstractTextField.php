<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

abstract class AbstractTextField extends AbstractField
{

    protected ?string $name;
    protected string $hint='';
    protected string $placeholder='';
    protected string $validator='';
    protected $maxChars;
    protected $minChars;

    public function __construct($fieldId, $name)
    {
        parent::__construct($fieldId);
        $this->name = $name;
    }

    public function toArray()
    {
        $definition=parent::toArray();
        $definition['name']=$this->name;
        $definition['hint']=$this->hint;
        $definition['placeholder']=$this->placeholder;
        $definition['validator']=$this->validator;
        $definition['max_chars']=$this->maxChars;
        $definition['min_chars']=$this->minChars;

        return $definition;
    }

    public function validateWith($validator)
    {
        $this->validator=$validator;
        return $this;
    }

    public function withHint($hint)
    {
        $this->hint=$hint;
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

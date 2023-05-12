<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

abstract class AbstractTextField extends AbstractField
{

    protected string $name;
    protected string $hint='';
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
        // TODO: Implement toArray() method.
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

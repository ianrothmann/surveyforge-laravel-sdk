<?php

namespace Surveyforge\Surveyforge\Definitions\Fields;

use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\CanBeUsedOnForms;
use Surveyforge\Surveyforge\Definitions\Fields\Interfaces\FieldType;

class TextInput extends AbstractTextField implements CanBeUsedOnForms
{
    protected string $type = FieldType::TEXT;

    protected $prefix;
    protected $suffix;

    protected $leadingIcon;
    protected $trailingIcon;

    /**
     * @param mixed $prefix
     * @return TextInput
     */
    public function withPrefix($prefix)
    {
        $this->prefix = $this->renderText($prefix);
        return $this;
    }

    /**
     * @param mixed $suffix
     * @return TextInput
     */
    public function withSuffix($suffix)
    {
        $this->suffix = $this->renderText($suffix);
        return $this;
    }

    /**
     * @param mixed $leadingIcon
     * @return TextInput
     */
    public function withLeadingIcon($leadingIcon)
    {
        $this->leadingIcon = $leadingIcon;
        return $this;
    }

    /**
     * @param mixed $trailingIcon
     * @return TextInput
     */
    public function withTrailingIcon($trailingIcon)
    {
        $this->trailingIcon = $trailingIcon;
        return $this;
    }



}

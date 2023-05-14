<?php

namespace Surveyforge\Surveyforge\Definitions\Content;

class QuestionContent extends AbstractContent
{
    protected string $type = self::QUESTION_BLOCK;

    protected ?string $question;
    protected ?string $header;
    protected ?string $subtitle;

    public function withQuestion($question)
    {
        $this->question=$question;
        return $this;
    }

    public function withHeader($header)
    {
        $this->header=$header;
        return $this;
    }

    public function withSubtitle($subtitle)
    {
        $this->subtitle=$subtitle;
        return $this;
    }

    public function toArray()
    {
        $definition=parent::toArray();
        $definition['header']=$this->header;
        $definition['question']=$this->question;
        $definition['subtitle']=$this->subtitle;

        return $definition;
    }
}

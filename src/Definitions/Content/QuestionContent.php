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
        $this->question=$this->renderText($question);
        return $this;
    }

    public function withHeader($header)
    {
        $this->header=$this->renderText($header);
        return $this;
    }

    public function withSubtitle($subtitle)
    {
        $this->subtitle=$this->renderText($subtitle);
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

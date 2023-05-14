<?php

namespace Surveyforge\Surveyforge\Definitions\Questions;

use Surveyforge\Surveyforge\Definitions\AnswerLayouts\AbstractAnswerLayout;
use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Content\AbstractContent;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

abstract class AbstractQuestion extends AbstractBuilder
{
    const HORIZONAL='horizontal';
    const VERTICAL='vertical';
    const STEP='step';

    protected $definitionType=DefinitionType::QUESTION;
    protected string $type;
    protected string $questionId;
    protected AbstractContent $questionContent;
    protected AbstractAnswerLayout $answer;

    public function __construct(string $questionId)
    {
        parent::__construct();
        $this->questionId=$questionId;
    }

    public function withQuestionContent(AbstractContent $content)
    {
        $this->questionContent=$content;
        return $this;
    }

    public function withAnswer(AbstractAnswerLayout $answerLayout)
    {
        $this->answer=$answerLayout;
        return $this;
    }

    public function toArray()
    {
        $definition=parent::toArray();
        $definition['type']=$this->type;
        $definition['question']=$this->questionContent->build();
        $definition['answer']=$this->answer->build();
        return $definition;
    }
}

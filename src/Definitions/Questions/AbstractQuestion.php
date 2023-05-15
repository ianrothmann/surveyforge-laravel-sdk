<?php

namespace Surveyforge\Surveyforge\Definitions\Questions;

use Surveyforge\Surveyforge\Definitions\AnswerLayouts\AbstractAnswerLayout;
use Surveyforge\Surveyforge\Definitions\AnswerLayouts\SingleAnswerLayout;
use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Condition\Condition;
use Surveyforge\Surveyforge\Definitions\Condition\ConditionHandlerTrait;
use Surveyforge\Surveyforge\Definitions\Content\AbstractContent;
use Surveyforge\Surveyforge\Definitions\Content\QuestionContent;
use Surveyforge\Surveyforge\Definitions\Fields\AbstractField;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

abstract class AbstractQuestion extends AbstractBuilder
{
    use ConditionHandlerTrait;

    const HORIZONAL='horizontal';
    const VERTICAL='vertical';

    protected $definitionType=DefinitionType::QUESTION;
    protected string $type;
    protected string $questionId;
    protected ?AbstractContent $questionContent=null;
    protected AbstractAnswerLayout $answer;
    protected $instructions;

    public function __construct(string $questionId)
    {
        parent::__construct();
        $this->questionId=$questionId;
        $this->instructions=collect();
    }

    public function withQuestionText($question,$header=null,$subtitle=null)
    {
        $this->questionContent=(new QuestionContent())
            ->withHeader($header)
            ->withQuestion($question)
            ->withSubtitle($subtitle);

        return $this;
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

    public function withAnswerField(AbstractField $field)
    {
        $this->answer=(new SingleAnswerLayout())
            ->withField($field);
        return $this;
    }

    public function addInstructions(AbstractContent $content)
    {
        $this->instructions->add($content);
        return $this;
    }

    public function toArray()
    {
        $definition=[];

        $definition['type']=$this->type;

        $definition['question']=$this->questionContent ? $this->questionContent->build() : null;

        $builtAnswer=$this->answer->build();

        $definition['answer_object']=[
            $this->questionId => $builtAnswer['answer_object']
        ];

        unset($builtAnswer['answer_object']);

        $definition['answer']=$builtAnswer;

        $definition['instructions']=$this->instructions->map(function(AbstractContent $content){
            $content->build();
        })->toArray();

        $definition['condition']=$this->condition ? $this->condition->build() : null;

        return $definition;
    }
}

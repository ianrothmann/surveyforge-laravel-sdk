<?php

namespace Surveyforge\Surveyforge\Definitions;

use Surveyforge\Surveyforge\Definitions\AnswerLayouts\AbstractAnswerLayout;
use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Condition\ConditionHandlerTrait;
use Surveyforge\Surveyforge\Definitions\Content\AbstractContent;
use Surveyforge\Surveyforge\Definitions\Content\HtmlContent;
use Surveyforge\Surveyforge\Definitions\Fields\AbstractField;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;
use Surveyforge\Surveyforge\Definitions\Questions\AbstractQuestion;
use Surveyforge\Surveyforge\Definitions\Questions\VerticalQuestion;

class Section extends AbstractBuilder
{
    use ConditionHandlerTrait;

    protected $definitionType=DefinitionType::SECTION;

    protected $instructions;
    protected $questions;

    protected $title;

    public function __construct()
    {
        parent::__construct();
        $this->instructions=collect();
        $this->questions=collect();
    }

    public function withTitle($title)
    {
        $this->title=$title;
        return $this;
    }

    public function addInstruction(AbstractContent $instruction)
    {
        $this->instructions->add($instruction);
        return $this;
    }

    public function addInstructionHtml(string $html)
    {
        $this->instructions->add(new HtmlContent($html));
        return $this;
    }

    public function addQuestion(AbstractQuestion $question)
    {
        $this->questions->add($question);
        return $this;
    }

    public function addQuestionStd($questionId, $questionText, AbstractField $answerField, $header=null,$subtitle=null)
    {
        $questionLayout=(new VerticalQuestion($questionId))
            ->withQuestionText($questionText,$header,$subtitle)
            ->withAnswerField($answerField);

        $this->questions->add($questionLayout);
        return $this;
    }

    public function toArray()
    {
        $definition=[
            'title'=>$this->title,
            'instructions'=>$this->instructions->map(function(AbstractContent $content){
                return $content->build();
            })->toArray(),
            'questions'=>$this->questions->map(function(AbstractQuestion $question){
                return $question->build();
            })->toArray(),
            'condition'=>$this->condition ? $this->condition->build() : null
        ];

        return $definition;
    }
}

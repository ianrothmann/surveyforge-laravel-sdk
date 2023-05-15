<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Surveys;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Condition\Condition;
use Surveyforge\Surveyforge\Definitions\Content\HtmlContent;
use Surveyforge\Surveyforge\Definitions\Fields\CheckboxGroup;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\Likert\AgreementLikert5;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\Likert\NeverAlwaysLikert5;
use Surveyforge\Surveyforge\Definitions\Predefined\Forms\BioForm;
use Surveyforge\Surveyforge\Definitions\Questions\StepQuestion;
use Surveyforge\Surveyforge\Definitions\Questions\VerticalQuestion;
use Surveyforge\Surveyforge\Definitions\Section;
use Surveyforge\Surveyforge\Definitions\Survey;

class DemoSurvey extends AbstractPredefinedBuilder
{
    public static function get(): Survey
    {
        $section1=(new Section())
            ->doNotShowWhen(function(Condition $condition){
                $condition->where('dogs','<=','1');
            })
            ->withTitle("About yourself")
            ->addInstructionHtml('We would like to get to know you.')
            ->addQuestion((new VerticalQuestion('bio'))
                ->withAnswer(BioForm::get())
                ->showWhen(function(Condition $condition){
                    $condition->where('dogs','>','0');
                })
            );

        $section2=(new Section())
            ->withTitle("Your feelings towards pets")
            ->addInstruction(new HtmlContent('The following section presents different pets. Please indicate the extent to which you <b>agree</b> with each statement by using the provided rating scale.'))
            ->addQuestionStd("dogs","I like dogs", AgreementLikert5::get('dogs'))
            ->addQuestionStd("cats","I like cats", AgreementLikert5::get('cats'))
            ->addQuestionStd("hamsters","I like hamsters", AgreementLikert5::get('hamsters'))
            ->addQuestionStd("dog_think","I think about my dog", NeverAlwaysLikert5::get('dog_think'));

        $section3=(new Section())
            ->withTitle("Final Section")
            ->addInstructionHtml('This is the final section\'s first instructions')
            ->addInstructionHtml('This is the final section\'s second instructions')
            ->addQuestion((new VerticalQuestion('final'))
                ->showWhen(function(Condition $condition){
                    $condition->where('dogs','>','3');
                })
                ->addInstructions(new HtmlContent('<b>Step 1</b>'))
                ->addInstructions(new HtmlContent('<b>Step 2</b>'))
                ->withAnswerField((new CheckboxGroup('dog_types','Which dogs do you like?'))
                    ->addOption('Bulldog','bulldog')
                    ->addOption('Labrador','labrador')
                ));

        $survey=(new Survey())
            ->withTitle('Pets')
            ->addOrientation(new HtmlContent('Orientation Screen'))
            ->addSection($section1)
            ->addSection($section2)
            ->addSection($section3)
            ->addEnding(new HtmlContent('Ending Screen'));

        return $survey;
    }
}

<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Surveys;

use Surveyforge\Surveyforge\Definitions\AnswerLayouts\SingleAnswerLayout;
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
            ->withTitle("About yourself")
            ->addInstructionHtml('We would like to get to know you.')
            ->addQuestion((new VerticalQuestion('bio'))
                ->withAnswer(BioForm::get())
            )->addQuestion((new VerticalQuestion('own_pets'))
                ->withQuestionText('Which of the following pets do you own?')
                ->withAnswer((new SingleAnswerLayout())
                    ->withField((new CheckboxGroup('pets','Which pets do you own?'))
                        ->addOption('Dogs','dog')
                        ->addOption('Cats','cat')
                        ->addOption('Hamsters','hamster'))
                )
            );

        $section2=(new Section())
            ->withTitle("Your feelings towards pets")
            ->addInstruction(new HtmlContent('The following section presents different pets. Please indicate the extent to which you <b>agree</b> with each statement by using the provided rating scale.'))
            ->addQuestionStd("dogs","I like my dog", AgreementLikert5::get('dogs'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
               // $condition->where('own_pets.pets.dog',1);
            })
            ->addQuestionStd("cats","I like my cat", AgreementLikert5::get('cats'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
              //  $condition->where('own_pets.pets.cat',1);
            })
            ->addQuestionStd("hamsters","I like my hamster", AgreementLikert5::get('hamsters'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
             //   $condition->where('own_pets.pets.hamster',1);
            })
            ->addQuestionStd("rabbit","I like my rabbit", AgreementLikert5::get('rabbits'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
             //   $condition->where('own_pets.pets.parrot',1);
            })
            ->addQuestionStd("parrots","I like my parrot", AgreementLikert5::get('parrots'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
              //  $condition->where('own_pets.pets.parrot',1);
            })
            ->addQuestionStd("lizard","I like my lizard", AgreementLikert5::get('lizards'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
               // $condition->where('own_pets.pets.lizard',1);
            });

        $section3=(new Section())
            ->withTitle("Final Section")
            ->addInstructionHtml('This is the final section\'s first instructions')
            ->addInstructionHtml('This is the final section\'s second instructions')
            ->addQuestion((new VerticalQuestion('final'))
                ->showWhen(function(Condition $condition){
                    $condition->where('dogs','>','2');
                })
                ->addInstructions(new HtmlContent('<b>Step 1</b>'))
                ->addInstructions(new HtmlContent('<b>Step 2</b>'))
                ->withQuestionText('Which of the following dogs do you like most?')
                ->withAnswerField((new CheckboxGroup('dog_types','Dogs'))
                    ->addOption('Bulldog','bulldog')
                    ->addOption('Labrador','labrador')
                    ->addOption('Any Dogs!','any', null, function(Condition $condition){
                        $condition->whereIn('dogs',[2,3,4,5]);
                        $condition->where(function(Condition $condition){
                            $condition->where('dogs','<','4')
                                      ->orWhere('hamsters','<','3');
                        });
                    })
                ));

        $survey=(new Survey())
            ->withTitle('Pets')
            ->addOrientation(new HtmlContent('Orientation Screen'))
          //  ->addSection($section1)
            ->addSection($section2)
         //   ->addSection($section3)
            ->addEnding(new HtmlContent('Ending Screen'));

        return $survey;
    }
}

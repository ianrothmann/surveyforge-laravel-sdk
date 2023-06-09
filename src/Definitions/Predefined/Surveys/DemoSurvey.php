<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Surveys;

use Surveyforge\Surveyforge\Definitions\AnswerLayouts\SingleAnswerLayout;
use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Condition\Condition;
use Surveyforge\Surveyforge\Definitions\Content\HtmlContent;
use Surveyforge\Surveyforge\Definitions\Fields\CheckboxGroup;
use Surveyforge\Surveyforge\Definitions\Fields\Dropdown;
use Surveyforge\Surveyforge\Definitions\Fields\NumberRating;
use Surveyforge\Surveyforge\Definitions\Fields\OptionsField;
use Surveyforge\Surveyforge\Definitions\Fields\RadioGroup;
use Surveyforge\Surveyforge\Definitions\Fields\TextArea;
use Surveyforge\Surveyforge\Definitions\Fields\TextInput;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\Likert\AgreementLikert5;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\Likert\NeverAlwaysLikert5;
use Surveyforge\Surveyforge\Definitions\Predefined\Forms\BioForm;
use Surveyforge\Surveyforge\Definitions\Questions\StepQuestion;
use Surveyforge\Surveyforge\Definitions\Questions\VerticalQuestion;
use Surveyforge\Surveyforge\Definitions\Section;
use Surveyforge\Surveyforge\Definitions\Survey;
use Surveyforge\Surveyforge\Definitions\Text\TextTranslator;

class DemoSurvey extends AbstractPredefinedBuilder
{
    public static function get(): Survey
    {
        $translator=new TextTranslator();
        $translator->addLanguage('en','English',true,'en')
            ->addLanguage('nl','Nederlands',true,'nl');

        $section1=(new Section())
            ->withTitle("About yourself")
            ->addInstructionHtml($translator->text('We would like to get to know you.')->translate('nl','We willen je graag leren kennen.'))
            ->addQuestion((new VerticalQuestion('bio'))
                ->withAnswer(BioForm::get())
            )->addQuestion((new VerticalQuestion('own_pets'))
                ->withQuestionText($translator->text('Do you own pets?')->translate('nl','Heb je huisdieren?'))
                ->withAnswer((new SingleAnswerLayout())
                    ->withField((new CheckboxGroup('pets',$translator->text('Do you own pets?')->translate('nl','Heb je huisdieren?')))
                        ->addOption($translator->text('Dogs')->translate('nl','Honden'),'dog')
                        ->addOption($translator->text('Cats')->translate('nl','Katten'),'cat')
                        ->addOption($translator->text('Hamsters')->translate('nl','Hamsters'),'hamster')
                    )
                )
            )->addQuestion((new VerticalQuestion('pet_name'))
                ->withQuestionText('What is the best Pet name ever?')
                ->withAnswer((new SingleAnswerLayout())
                    ->withField((new TextInput('pet_name','Name')))
                )
            )->addQuestion((new VerticalQuestion('describe_pets'))
                ->withQuestionText('Describe your love of pets')
                ->withAnswer((new SingleAnswerLayout())
                    ->withField((new TextArea('describe','Describe your love of pets')))
                )
            )->addQuestion((new VerticalQuestion('rate_self'))
                ->withQuestionText('I am a pet person')
                ->withAnswer((new SingleAnswerLayout())
                    ->withField((new NumberRating('rate_self'))->withRange(1,10,"Not at all","Very much"))
                ))
                ->addQuestion((new VerticalQuestion('football_team'))
                ->withQuestionText('Which football team do you support?')
                ->withAnswer((new SingleAnswerLayout())
                    ->withField((new OptionsField('team'))
                        ->addOption('Ajax','ajax')
                        ->addOption('PSV','psv')
                        ->addOption('Feyenoord','feyenoord')
                        ->addOption('AZ','az')
                        ->addOption('FC Utrecht','utrecht')
                        ->addOption('FC Twente','twente')
                        ->addOption('FC Groningen','groningen')
                        ->addOption('FC Emmen','emmen')
                        ->addOption('FC Heerenveen','heerenveen')
                        ->addOption('FC Den Haag','denhaag')
                        ->addOption('FC VVV','vvv')
                    )
                )

                )->addQuestion((new VerticalQuestion('rugby_team'))
                ->withQuestionText('Which rugby team do you support?')
                ->withAnswer((new SingleAnswerLayout())
                    ->withField((new RadioGroup('team','Which rugby team do you support?'))
                        ->addOption('France','france')
                        ->addOption('England','england')
                        ->addOption('Ireland','ireland')
                        ->addOption('Scotland','scotland')
                        ->addOption('Wales','wales')
                    ))
                );

        $section2=(new Section())
            ->withTitle("Your feelings towards pets")
            ->addInstruction(new HtmlContent('The following section presents different pets. Please indicate the extent to which you <b>agree</b> with each statement by using the provided rating scale.'))
            ->addQuestionStd("dogs","I like my dog", AgreementLikert5::get('dogs'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
                $condition->whereIn('own_pets.pets','dog');
            })
            ->addQuestionStd("cats","I like my cat", AgreementLikert5::get('cats'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
                $condition->whereIn('own_pets.pets','cat');
            })
            ->addQuestionStd("hamsters","I like my hamster", AgreementLikert5::get('hamsters'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
                $condition->whereIn('own_pets.pets','hamster');
            })
            ->addQuestionStd("rabbit","I like my rabbit", AgreementLikert5::get('rabbits'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
                $condition->whereIn('own_pets.pets','parrot');
            })
            ->addQuestionStd("parrots","I like my parrot", AgreementLikert5::get('parrots'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
                $condition->whereIn('own_pets.pets','parrot');
            })
            ->addQuestionStd("lizard","I like my lizard", AgreementLikert5::get('lizards'), 'Please Answer', 'Select an option to continue', function(Condition $condition){
                $condition->whereIn('own_pets.pets','lizard');
            });

        $section3=(new Section())
            ->withTitle("Final Section")
            ->addInstructionHtml('This is the final section\'s first instructions')
            ->addInstructionHtml('This is the final section\'s second instructions')
            ->addQuestion((new VerticalQuestion('final_dropdown'))
                ->addInstructions(new HtmlContent('<b>Step 1</b>'))
                ->withQuestionText('Which of the following dogs do you like most?')
                ->withAnswerField((new Dropdown('final_dropdown','Dogs'))
                    ->addOption('Bulldog','bulldog')
                    ->addOption('Labrador','labrador')
                    ->addOption('Any Dogs!','any', 'I am a dog lover')
                ))
            ->addQuestion((new VerticalQuestion('final'))
                ->showWhen(function(Condition $condition){
                    $condition->where('dogs','>','2')
                        ->whereNotNull('dogs');
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
            ->withTranslator($translator)
            ->withTitle('Pets')
            ->addOrientation(new HtmlContent('Orientation Screen'))
            ->addSection($section1)
            ->addSection($section2)
            ->addSection($section3)
            ->addEnding(new HtmlContent('Ending Screen'));

        return $survey;
    }
}

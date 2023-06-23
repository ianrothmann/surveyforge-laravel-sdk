<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Forms;

use Surveyforge\Surveyforge\Definitions\AnswerLayouts\FormAnswerLayout;
use Surveyforge\Surveyforge\Definitions\Fields\CheckboxGroup;
use Surveyforge\Surveyforge\Definitions\Fields\Dropdown;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\Radio\GenderField;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\Text\FirstNameField;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\Text\LastNameField;

class BioForm extends AbstractPredefinedBuilder
{
    public static function get(): FormAnswerLayout
    {

        $dropDown=(new Dropdown('citizen','Citizenship'))
            ->addOption('Dutch','dutch')
            ->addOption('Belgian','belgian')
            ->addOption('German','german')
            ->addOption('French','french')
            ->addOption('English','english')
            ->addOption('Other','other');

        $checkboxGroup=(new CheckboxGroup('interests','What are your interests?'))
            ->addOption('Sports','sports')
            ->addOption('Music','music')
            ->addOption('Reading','reading')
            ->addOption('Movies','movies')
            ->addOption('Cooking','cooking')
            ->addOption('Other','other');


        return (new FormAnswerLayout())
            ->numberOfGridColumns(2)
            ->addField(FirstNameField::get())
            ->addField(LastNameField::get())
            ->addField(GenderField::get())
            ->addField($dropDown)
            ->addField($checkboxGroup);
    }
}

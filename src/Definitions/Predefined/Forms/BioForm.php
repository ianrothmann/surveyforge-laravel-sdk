<?php

namespace Surveyforge\Surveyforge\Definitions\Predefined\Forms;

use Surveyforge\Surveyforge\Definitions\AnswerLayouts\FormAnswerLayout;
use Surveyforge\Surveyforge\Definitions\Predefined\AbstractPredefinedBuilder;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\Radio\GenderField;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\Text\FirstNameField;
use Surveyforge\Surveyforge\Definitions\Predefined\Fields\Text\LastNameField;

class BioForm extends AbstractPredefinedBuilder
{
    public static function get(): FormAnswerLayout
    {
        return (new FormAnswerLayout())
            ->numberOfGridColumns(2)
            ->addField(FirstNameField::get())
            ->addField(LastNameField::get())
            ->addField(GenderField::get(),2);
    }
}

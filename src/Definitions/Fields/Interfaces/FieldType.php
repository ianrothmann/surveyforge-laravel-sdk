<?php

namespace Surveyforge\Surveyforge\Definitions\Fields\Interfaces;

interface FieldType
{
    const TEXT = 'text';
    const TEXT_AREA = 'textarea';
    const RADIO_GROUP = 'radio_group';
    const CHECKBOX_GROUP = 'checkbox_group';
    const DROPDOWN = 'dropdown';
    const DATE = 'date';
    const TIME = 'time';
    const DATETIME = 'datetime';
    const LIKERT = 'likert';
    const NUMBER_RATING = 'number_rating';
    const MULTI_SELECT = 'multi_select';
    const OPTIONS = 'options';

    const PHONE = 'phone';
    const COUNTRY = 'country';
    const URL = 'url';
}

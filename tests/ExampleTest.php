<?php

use Surveyforge\Surveyforge\Expression\Expression;

it('can build a survey', function () {
    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get();
    expect($survey->build())->toBeArray();
});


it('can extract a survey flow from the demo survey', function () {
    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get();
    $def=$survey->build();
    $creator=new \Surveyforge\Surveyforge\Flow\SurveyFlowCreator($def);
    $flow=$creator->get();
    expect($flow)->toHaveKeys(['answer_object','sections','flow','conditions']);

});

it('can run successfully through the survey flow',function() {
    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get();
    $state=\Surveyforge\Surveyforge\State\SurveyStateHandler::fromSurvey($survey);
    $state->setAnswer('own_pets.pets.dog',1);
    $state->setAnswer('own_pets.pets.cat',1);
    $state->setAnswer('own_pets.pets.hamster',0);
    $state->setAnswer('dogs.dogs',3);

    $state->next();
    $state->next();
    $state->next();
    $state->next();
    $state->next();
    $state->next();

    //Skip the hamster question
    expect($state->getCurrentFlowItem()['html'])->toBe('This is the final section\'s first instructions');

});

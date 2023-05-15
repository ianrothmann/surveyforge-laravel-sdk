<?php

it('can build a survey', function () {
    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get();
   // dd($survey->build());
    expect($survey->build())->toBeArray();

});


it('can extract a survey flow from the demo survey', function () {
    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get();
    $def=$survey->build();
    $creator=new \Surveyforge\Surveyforge\Flow\SurveyFlowCreator($def);

    dd($creator);

    //expect($survey->build())->toBeArray();

});

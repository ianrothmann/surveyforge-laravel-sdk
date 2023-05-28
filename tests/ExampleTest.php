<?php

it('can build a survey', function () {
    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get();
   // dd($survey->build());
    expect($survey->build())->toBeArray();
    //dd($survey->build());
});


it('can extract a survey flow from the demo survey', function () {
    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get();
    $def=$survey->build();
    $creator=new \Surveyforge\Surveyforge\Flow\SurveyFlowCreator($def);

    $flow=$creator->get();
    dd($flow->toJson());
    dd($flow->get('flow'),$flow->get('conditions'));
    $answers=$flow->get('answer_object');
    \Illuminate\Support\Arr::set($answers,'bio.first_name','John');
    dd(\Illuminate\Support\Arr::get($answers,'bio'));
dd($creator->get());
    dd($creator->get()->toJson());

    //expect($survey->build())->toBeArray();

});

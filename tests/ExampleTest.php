<?php

use Surveyforge\Surveyforge\Expression\Expression;
/*
it('build translations', function () {
    $bag=new \Surveyforge\Surveyforge\Definitions\Text\TextTranslator();
    $bag->addLanguage('en','English',true,'en')
        ->addLanguage('de','German',false,'en');

    $bag->text('This is the english text')
        ->translate('de','Das ist der deutsche Text');

    $text=collect($bag->build()['texts'])->first();
    expect($text['text'])->toHaveLength(2);
});
*/
/*
it('can build a survey', function () {
    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get();
    expect($survey->build())->toBeArray();
});


it('can extract a survey flow from the demo survey', function () {
    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get();
    $def=$survey->build();
    $creator=new \Surveyforge\Surveyforge\Flow\SurveyFlowCreator($def);
    $flow=$creator->get();

   // dd($flow->get('flow'));
    expect($flow)->toHaveKeys(['answer_object','sections','flow','conditions']);

});

*/
/*
it('can run successfully through the survey flow',function() {
    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get();
    $state=\Surveyforge\Surveyforge\State\SurveyStateHandler::fromSurvey($survey);
    $state->setAnswer('own_pets.pets',['dog','cat']);
    $state->setAnswer('own_pets.pets.cat',1);
    $state->setAnswer('dogs.dogs',3);
   // dd($state->getInvalidFlowItems());

    $state->next();
    $state->next();
    $state->next();
    $state->next();
    $state->next();
    $state->next();
    $state->next();
    $state->next();
    $state->next();
    //Skip the hamster question
    expect($state->getCurrentFlowItem()['html'])->toBe('This is the final section\'s first instructions');

});
*/
/*
it('can create and validate signed urls', function(){
    $url='https://google.com?name=test&age=30';
    $signer=\Surveyforge\Surveyforge\Url\SurveyforgeUrlSigner::withSecret('123456');
    $signedUrl=$signer->sign($url);

    expect($signer->check($signedUrl))->toBeTrue();
    expect($signer->check(\Illuminate\Support\Str::replace('google','yahoo',$signedUrl)))->toBeFalse();

    $url='https://google.com';
    $signer=\Surveyforge\Surveyforge\Url\SurveyforgeUrlSigner::withSecret('123456');
    $signedUrl=$signer->sign($url);
    expect($signer->check($signedUrl))->toBeTrue();
    expect($signer->check(\Illuminate\Support\Str::replace('https:','http:',$signedUrl)))->toBeTrue();

});

it('can evaluate a in_array',function(){
    $ex=new Surveyforge\Surveyforge\Expression\Expression('in_array(arr1,arr2)');
    $ex->withVariables([
        'arr1'=>[1,2,3],
        'arr2'=>[5],
    ]);
    dd($ex->evaluate());
});
*/
it('can create a survey on surveyforge server', function(){

    $survey=\Surveyforge\Surveyforge\Definitions\Predefined\Surveys\DemoSurvey::get()
        ->enableActivityProctoring()
        ->setTimeLimit(300);

    //$creator = new \Surveyforge\Surveyforge\Flow\SurveyFlowCreator($survey->build());
    //dd($creator->get());

    $deployedSurvey=new \Surveyforge\Surveyforge\Deployment\DeployedSurvey();
    $deployedSurvey->onConnection('http://localhost:8021','PJ4Zuj5l4Tt9O61cieEeaqzKuWDqpJKE05rTWrm3');
    $deployedSurvey->setDefinition($survey->build());
    $deployedSurvey->setSurvey($survey);
    $deployedSurvey->redirectTo('https://google.com');
    $deployedSurvey->setTags(['test','demo']);
    $deployedSurvey->save();
dd();
    $deployedSurvey=new \Surveyforge\Surveyforge\Deployment\DeployedSurvey($deployedSurvey->surveyId);
    $deployedSurvey->onConnection('http://localhost:8021','9952747e-ea51-410c-917f-6312d716f18b|YaWqpHg3ylZ7hxdA6yIT4Sc2LxocJQuqGA3g8DC0');
    $deployedSurvey->get();
    $deployedSurvey->setTags(['test','demo','test2']);
    $deployedSurvey->expiresAfter(\Illuminate\Support\Carbon::now()->addMonths(2));
    $deployedSurvey->save();


dd($deployedSurvey->getDefinition(), $deployedSurvey->getUrl());

    dd($deployedSurvey->refresh()->getUrl(),$deployedSurvey->refresh()->getToken());


});



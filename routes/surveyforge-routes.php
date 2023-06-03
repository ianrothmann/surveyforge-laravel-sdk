<?php

use Illuminate\Support\Facades\Route;
use Surveyforge\Surveyforge\Controllers\SurveyforgeRedirectController;

Route::group(['prefix' => 'surveyforge'],function(){
    Route::get('redirect',[SurveyforgeRedirectController::class,'redirect'])->name('surveyforge.sdk.redirect');
});

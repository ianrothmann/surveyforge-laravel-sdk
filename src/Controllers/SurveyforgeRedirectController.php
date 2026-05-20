<?php

namespace Surveyforge\Surveyforge\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Surveyforge\Surveyforge\Deployment\DeployedSurvey;
use Surveyforge\Surveyforge\Events\SurveyForgeCompleteEvent;
use Surveyforge\Surveyforge\Events\SurveyForgePauseEvent;
use Surveyforge\Surveyforge\Request\RedirectContext;
use Surveyforge\Surveyforge\Request\RedirectRequestHandler;

class SurveyforgeRedirectController extends Controller
{
    public function redirect(Request $request)
    {
        RedirectRequestHandler::forRequest($request)
            ->onPause(function (DeployedSurvey $survey, RedirectContext $context) {
                event(new SurveyForgePauseEvent($survey, $context));
            })
            ->onComplete(function (DeployedSurvey $survey, RedirectContext $context) {
                event(new SurveyForgeCompleteEvent($survey, $context));
            })
            ->onSecurityValidationFailed(function () {
                abort(401);
            })
            ->handle();

        abort(400);
    }
}

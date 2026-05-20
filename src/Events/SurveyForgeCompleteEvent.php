<?php

namespace Surveyforge\Surveyforge\Events;

use Illuminate\Queue\SerializesModels;
use Surveyforge\Surveyforge\Deployment\DeployedSurvey;
use Surveyforge\Surveyforge\Request\RedirectContext;

class SurveyForgeCompleteEvent
{
    use SerializesModels;

    public DeployedSurvey $survey;

    public ?RedirectContext $context;

    public function __construct(DeployedSurvey $survey, ?RedirectContext $context = null)
    {
        $this->survey = $survey;
        $this->context = $context;
    }
}

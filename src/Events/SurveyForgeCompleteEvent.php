<?php

namespace Surveyforge\Surveyforge\Events;

use Illuminate\Queue\SerializesModels;
use Surveyforge\Surveyforge\Deployment\DeployedSurvey;

class SurveyForgeCompleteEvent
{
    use SerializesModels;

    public DeployedSurvey $survey;

    /**
     * Create a new event instance.
     *
     * @param  DeployedSurvey  $survey
     * @return void
     */
    public function __construct(DeployedSurvey $survey)
    {
        $this->survey = $survey;
    }

}

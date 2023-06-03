<?php

namespace Surveyforge\Surveyforge\Deployment\Traits;

trait HandlesSurveyApiCalls
{
    use HandlesApiCalls;

    protected function querySurveys($surveyId, $params=[])
    {
        return $this->api->get('surveys',$params);
    }

    protected function getSurvey($surveyId)
    {
        return $this->api->get('surveys/'.$surveyId)->get('survey');
    }

    protected function createSurvey($data)
    {
        return $this->api->post('surveys',$data)->get('survey');
    }

    protected function patchSurvey($surveyId, $data)
    {
        return $this->api->patch('surveys/'.$surveyId,$data)->get('survey');
    }

    protected function deleteSurvey($surveyId)
    {
        return $this->api->delete('surveys');
    }
}

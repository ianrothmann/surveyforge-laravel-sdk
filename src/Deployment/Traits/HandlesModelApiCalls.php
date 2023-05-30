<?php

namespace Surveyforge\Surveyforge\Deployment\Traits;

use Illuminate\Support\Facades\Http;
use Surveyforge\Surveyforge\Deployment\Api\SurveyforgeApi;

trait HandlesModelApiCalls
{
    private SurveyforgeApi $api;

    protected function setApiFromConfig()
    {
        $this->api=new SurveyforgeApi(config('surveyforge.servers.default.url'),config('surveyforge.default.token'));
    }

    public function withApi(SurveyforgeApi $api)
    {
        $this->api=$api;
        return $this;
    }

    public function onServer($profileName)
    {
        $this->api=new SurveyforgeApi(config('surveyforge.servers.'.$profileName.'.url'),config('surveyforge.'.$profileName.'.token'));
        return $this;
    }

    public function onConnection($serverUrl, $authToken)
    {
        $this->api=new SurveyforgeApi($serverUrl, $authToken);
        return $this;
    }

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

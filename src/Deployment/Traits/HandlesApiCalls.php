<?php

namespace Surveyforge\Surveyforge\Deployment\Traits;

use Illuminate\Support\Facades\Http;
use Surveyforge\Surveyforge\Deployment\Api\SurveyforgeApi;

trait HandlesApiCalls
{
    private SurveyforgeApi $api;

    protected function setApiFromConfig()
    {
        $this->api=new SurveyforgeApi(config('surveyforge.servers.default.url'),config('surveyforge.servers.default.token'));
    }

    public function withApi(SurveyforgeApi $api)
    {
        $this->api=$api;
        return $this;
    }

    public function onServer($profileName)
    {
        $this->api=new SurveyforgeApi(config('surveyforge.servers.'.$profileName.'.url'),config('surveyforge.servers.'.$profileName.'.token'));
        return $this;
    }

    public function onConnection($serverUrl, $authToken)
    {
        $this->api=new SurveyforgeApi($serverUrl, $authToken);
        return $this;
    }
}

<?php

namespace Surveyforge\Surveyforge\Deployment;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Surveyforge\Surveyforge\Deployment\Traits\HandlesApiCalls;

class SurveyforgeVerifier
{
    use HandlesApiCalls;

    public function __construct()
    {
        $this->setApiFromConfig();
    }

    public static function withDefaultConfig()
    {
        return new static();
    }

    public function verifyUrl($url)
    {
        $response=Cache::remember($this->getCacheKey($url), $this->getCacheTimeout($url), function () use ($url) {
            return $this->api->post('verify/url', [
                'url'=>$url
            ]);
        });
        return $response->get('valid');
    }

    public function verifyCurrentUrl()
    {
        return $this->verifyUrl(URL::full());
    }

    protected function getCacheKey($url)
    {
        return 'surveyforge:verify:url:'.md5($url);
    }

    protected function getCacheTimeout($url)
    {
        if(App::hasDebugModeEnabled()){
            return -1;
        }

        $query_parts=$this->getQueryParts($url);
        $seconds=60;
        if(array_key_exists('expires', $query_parts)){
            $carbon=Carbon::createFromTimestamp($query_parts['expires']);
            $seconds=$carbon->diffInSeconds(Carbon::now());
        }
        return $seconds;
    }

    protected function getQueryParts($url)
    {
        $queryString=parse_url($url, PHP_URL_QUERY);
        $query_parts=[];
        parse_str($queryString ?? '', $query_parts);
        return $query_parts;
    }
}

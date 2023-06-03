<?php

namespace Surveyforge\Surveyforge\Deployment\Api;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class SurveyforgeApi
{
    protected $url;
    protected $authToken;

    public function __construct($serverEndpoint, $authToken)
    {
        $this->url = rtrim($serverEndpoint, '/').'/api/v1';
        $this->authToken = $authToken;
    }

    protected function createCurlRequest($endpoint, $method, $data = [])
    {
        $url = $this->url.'/'.$endpoint;

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$this->authToken,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 180,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        if ($method !== 'GET') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

            if(in_array($method,['POST','PATCH'])){
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        return $curl;
    }

    protected function executeCurlRequest($curl): Collection
    {
        $response = curl_exec($curl);
        $error = curl_error($curl);
        //get curl code
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error || !in_array($code, [200, 201, 204])) {
            throw new Exception('CURL Error: '.$error.' - '.$response);
        }

        $result=collect(json_decode($response, true));

        if($result->get('message')=='Unauthenticated.'){
            throw new Exception('API Error: Unauthenticated or Authentication token is invalid.');
        }

        if($result->has('error')){
            if(App::hasDebugModeEnabled() || true){
                throw new Exception('API Error: '.$result->get('error').': '.$result->get('message'));
            }else{
                throw new Exception('API Error: '.$result->get('error'));
            }
        }

        return $result;
    }

    public function get($endpoint, $params = []): Collection
    {
        $url = $this->url.'/'.$endpoint;
        if (!empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        $curl = $this->createCurlRequest($endpoint, 'GET');
        curl_setopt($curl, CURLOPT_URL, $url);

        return $this->executeCurlRequest($curl);
    }

    public function post($endpoint, $data = []): Collection
    {
        $curl = $this->createCurlRequest($endpoint, 'POST', $data);

        return $this->executeCurlRequest($curl);
    }

    public function patch($endpoint, $data = []): Collection
    {
        $curl = $this->createCurlRequest($endpoint, 'PATCH', $data);

        return $this->executeCurlRequest($curl);
    }

    public function delete($endpoint): Collection
    {
        $curl = $this->createCurlRequest($endpoint, 'DELETE');

        return $this->executeCurlRequest($curl);
    }
}

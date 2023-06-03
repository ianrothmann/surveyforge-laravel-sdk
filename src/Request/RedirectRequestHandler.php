<?php

namespace Surveyforge\Surveyforge\Request;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Surveyforge\Surveyforge\Deployment\Api\SurveyforgeApi;
use Surveyforge\Surveyforge\Deployment\DeployedSurvey;
use Surveyforge\Surveyforge\Deployment\SurveyforgeVerifier;
use Surveyforge\Surveyforge\Deployment\Traits\HandlesApiCalls;
use Surveyforge\Surveyforge\Url\SurveyforgeUrlSigner;

class RedirectRequestHandler
{
    use HandlesApiCalls;

    protected Request $request;
    protected $onPause;
    protected $onComplete;
    protected $onExpire;
    protected $onSecurityFailed;

    public function __construct(Request $request)
    {
        $this->request=$request;
    }

    public static function forRequest(Request $request)
    {
        return (new static($request));
    }

    public function onPause($urlOrcallable)
    {
        $this->onPause=$urlOrcallable;
        return $this;
    }

    public function onComplete($urlOrcallable)
    {
        $this->onComplete=$urlOrcallable;
        return $this;
    }

    public function onExpire($urlOrcallable)
    {
        $this->onExpire=$urlOrcallable;
        return $this;
    }

    public function onSecurityValidationFailed($urlOrcallable)
    {
        $this->onSecurityFailed=$urlOrcallable;
        return $this;
    }

    public function handle()
    {
        $validator=Validator::make($this->request->all(),[
            'survey_id'=>'required|uuid',
            'server_id'=>'required|string',
            'signature'=>'required|string',
            'action'=>'required|in:pause,complete,expire',
        ]);

        if(!$validator->valid()){
            abort(400);
        }

        $this->handleSecurityValidation();

        if($this->request->get('action')==='pause'){
            return $this->handlePause();
        }else if($this->request->get('action')=='complete'){
            return $this->handleComplete();
        }else if($this->request->get('action')=='expire'){
            return $this->handleExpire();
        }else{
            throw new \Exception('Invalid request');
        }
    }

    protected function handlePause()
    {
        if($this->onPause && is_callable($this->onPause)){
            $survey=$this->getDeployedSurvey();
            return call_user_func($this->onPause,$survey);
        }elseif ($this->onPause){
            Redirect::to($this->onPause);
        }
    }

    protected function handleComplete()
    {
        if($this->onComplete && is_callable($this->onComplete)){
            $survey=$this->getDeployedSurvey();
            return call_user_func($this->onComplete,$survey);
        }elseif ($this->onComplete){
            Redirect::to($this->onComplete);
        }
    }

    protected function handleExpire()
    {
        if($this->onExpire && is_callable($this->onExpire)){
            return call_user_func($this->onExpire);
        }
        $deployedSurvey=$this->getDeployedSurvey();
        $url=$deployedSurvey->getUrl();
        Redirect::to($url);
    }

    protected function handleSecurityValidation()
    {
        if(!$this->api){
            $serverConfig=$this->findServerConfig($this->request->get('server_id'));
            $this->api=new SurveyforgeApi($serverConfig['url'],$serverConfig['token']);
        }

        $verifier=new SurveyforgeVerifier();
        $verifier->withApi($this->api);
        $success=$verifier->verifyCurrentUrl();

        if(!$success){
            if($this->onSecurityFailed && is_callable($this->onSecurityFailed)){
                return call_user_func($this->onSecurityFailed);
            }elseif ($this->onSecurityFailed){
                Redirect::to($this->onSecurityFailed);
            }else{
                abort(401);
            }
        }
    }

    protected function findServerConfig($url)
    {
        $serverId=$this->request->get('server_id');
        $server=collect(config('surveyforge.servers'))->where('id',$serverId)->first();
        if(!$server){
            throw new \Exception('SurveyForge server ID not found in config: '.$serverId);
        }
        return $server;
    }

    protected function getDeployedSurvey(): DeployedSurvey
    {
        $deployedSurvey=DeployedSurvey::find($this->request->get('survey_id'));
        if(!$deployedSurvey){
            throw new \Exception('Invalid survey id');
        }
        return $deployedSurvey;
    }

}

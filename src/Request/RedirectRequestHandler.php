<?php

namespace Surveyforge\Surveyforge\Request;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Redirect;
use Surveyforge\Surveyforge\Deployment\DeployedSurvey;
use Surveyforge\Surveyforge\Url\SurveyforgeUrlSigner;

class RedirectRequestHandler
{

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
        $this->request->validate([
            'survey_id'=>'required|uuid',
            'signature'=>'required|string',
        ]);

        $this->handleSecurityValidation();

        if($this->request->has('surveyforge_pause')){
            return $this->handlePause();
        }else if($this->request->has('surveyforge_complete')){
            return $this->handleComplete();
        }else if($this->request->has('surveyforge_expire')){
            return $this->handleExpire();
        }else{
            throw new \Exception('Invalid request');
        }
    }

    protected function handlePause()
    {
        if($this->onPause && is_callable($this->onPause)){
            return call_user_func($this->onPause);
        }elseif ($this->onPause){
            Redirect::to($this->onPause);
        }
    }

    protected function handleComplete()
    {
        if($this->onComplete && is_callable($this->onComplete)){
            return call_user_func($this->onComplete);
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
        $deployedSurvey=$this->getDeployedSurvey();
        $success=SurveyforgeUrlSigner::withSecret($deployedSurvey->getToken())
            ->check($this->request->fullUrl());

        if(!$success){
            if($this->onSecurityFailed && is_callable($this->onSecurityFailed)){
                return call_user_func($this->onSecurityFailed);
            }elseif ($this->onSecurityFailed){
                Redirect::to($this->onSecurityFailed);
            }else{
                die('Security validation failed');
            }
        }
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

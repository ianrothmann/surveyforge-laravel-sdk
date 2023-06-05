<?php

namespace Surveyforge\Surveyforge\Deployment;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Surveyforge\Surveyforge\Definitions\Survey;
use Surveyforge\Surveyforge\Deployment\Traits\HandlesSurveyApiCalls;

class DeployedSurvey
{
    use HandlesSurveyApiCalls;

    public $surveyId;
    protected $surveyData;
    protected $answerData;
    protected $dirty;
    protected $redirectDisabled=false;

    public function __construct($surveyId=null)
    {
        $this->setApiFromConfig();
        $this->dirty=collect();
        $this->surveyData=collect();
        $this->answerData=collect();
        $this->surveyId=$surveyId;
    }

    public function setDefinition($definition)
    {
        $this->dirty->put('definition',$definition);
        return $this;
    }

    public function setSurvey(Survey $survey)
    {
        $this->dirty->put('definition',$survey->toArray());
        return $this;
    }

    public function setTags($tags=[])
    {
        $this->dirty->put('tags',$tags);
        return $this;
    }

    public function expiresAfter(Carbon $dateTime)
    {
        $this->dirty->put('expires_at',$dateTime->toDateTimeString());
        return $this;
    }

    public function notifyWhenComplete($url)
    {
        $this->dirty->put('webhook_on_complete',$url);
        return $this;
    }

    public function redirectTo($url)
    {
        $this->dirty->put('redirect_to',$url);
        return $this;
    }

    public function disableRedirect($disable=true)
    {
        $this->dirty->put('redirect_to',null);
        $this->redirectDisabled=$disable;
        return $this;
    }

    public function toBeDeletedAfter(Carbon $dateTime)
    {
        $this->dirty->put('to_be_deleted_at',$dateTime->toDateTimeString());
        return $this;
    }

    public function linkToBot($botId)
    {
        $this->dirty->put('bot_id',$botId);
        return $this;
    }

    public function getAnswers()
    {
        return $this->answerData;
    }

    public function getAnswersDot()
    {
        return collect(Arr::dot($this->answerData->toArray()));
    }

    public function getUrl()
    {
        return $this->surveyData->get('survey_url');
    }

    public function getDefinition()
    {
        return $this->surveyData->get('definition');
    }

    public function getExpiresAt()
    {
        return $this->surveyData->get('expires_at');
    }

    public function getToBeDeletedAt()
    {
        return $this->surveyData->get('to_be_deleted_at');
    }

    public function getTags()
    {
        return $this->surveyData->get('tags');
    }

    public function getRedirectUrl()
    {
        return $this->surveyData->get('redirect_to');
    }

    public function getCompleteNotificationUrl()
    {
        return $this->surveyData->get('webhook_on_complete');
    }

    public function getBotId()
    {
        return $this->surveyData->get('bot_id');
    }

    public static function find($surveyId)
    {
        $model=new self($surveyId);
        $model->get();
        return $model;
    }

    public function get()
    {
        if($this->surveyData->count()==0 && $this->surveyId){
            $this->refresh();
        }

        return $this;
    }

    public function refresh()
    {
        if($this->surveyId){
            $this->hydrate($this->getSurvey($this->surveyId));
        } elseif (!$this->surveyId){
            throw new \Exception('Survey id not set');
        }

        return $this;
    }

    public function hydrate($data)
    {
        $data=collect($data);
        if($data->get('id')){
            $this->surveyData=$data;
            $this->surveyId=$data['id'];
        }elseif($data->get('survey') && $data->get('answers')){
            $surveyData=collect(collect($data)->get('survey'));
            $this->surveyData=$surveyData;
            $this->surveyId=$surveyData['id'];
            $this->answerData=collect(collect($data)->get('answers'));
        }else{
            throw new \Exception('Survey data is not valid');
        }
        return $this;
    }

    public function save()
    {
        if($this->surveyId){
            if($this->dirty->count()>0){
                $this->hydrate($this->patchSurvey($this->surveyId,$this->dirty));
            }
        }else{
            if($this->dirty->get('definition')){
                $this->createRedirectUrl();
                $this->hydrate($this->createSurvey($this->dirty));
            }else{
                throw new \Exception('Survey data does not contain a definition as cannot create');
            }
        }

        return $this;
    }

    protected function createRedirectUrl()
    {
        if($this->redirectDisabled){
            $this->redirectTo(null);
            return;
        }

        if($this->dirty->get('redirect_to')){
            return;
        }

        if(config('surveyforge.redirect_route')){
            $this->redirectTo(route(config('surveyforge.redirect_route')));
        }else{
            $this->redirectTo(route('surveyforge.sdk.redirect'));
        }
    }
}

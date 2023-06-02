<?php

namespace Surveyforge\Surveyforge\Deployment;

use Illuminate\Support\Carbon;
use Surveyforge\Surveyforge\Definitions\Survey;
use Surveyforge\Surveyforge\Deployment\Traits\HandlesModelApiCalls;

class DeployedSurvey
{
    use HandlesModelApiCalls;

    public $surveyId;
    protected $surveyData;
    protected $dirty;

    public function __construct($surveyId=null)
    {
        $this->setApiFromConfig();
        $this->dirty=collect();
        $this->surveyData=collect();
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

    public function getUrl()
    {
        return $this->surveyData->get('survey_url');
    }

    public function getToken()
    {
        return $this->surveyData->get('token');
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

    public function hydrate($surveyData)
    {
        $surveyData=collect($surveyData);

        if($surveyData->get('id')){
            $this->surveyData=$surveyData;
            $this->surveyId=$surveyData['id'];
        }else{
            throw new \Exception('Survey data does not contain an id');
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
                $this->hydrate($this->createSurvey($this->dirty));
            }else{
                throw new \Exception('Survey data does not contain a definition as cannot create');
            }
        }

        return $this;
    }
}

<?php

namespace Surveyforge\Surveyforge\Definitions\Text;

use Illuminate\Support\Arr;
use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

class TextTranslator extends AbstractBuilder
{

    protected $definitionType=DefinitionType::TEXT_BAG;
    protected $textBag=[];
    protected $languages;

    public function __construct()
    {
        parent::__construct();
        $this->textBag=[];
        $this->languages=collect();
    }

    public function text($text, $languageId=null)
    {
        if(!$languageId){
            $lang=$this->getDefaultLanguage();
            if(!$lang){
                throw new \Exception('No default language set and no language Id was provided.');
            }
            $languageId=$lang['id'];
        }

        $textDefinition=new Text($this->languages->keys()->toArray());
        $this->textBag[$textDefinition->getId()]=$textDefinition;
        return $this->textBag[$textDefinition->getId()]->translate($languageId,$text);
    }

    public function getDefaultLanguage()
    {
        return $this->languages->where('default',true)->first();
    }

    public function addLanguage($languageId, $name, $default=false, $systemLanguage='en')
    {
        $this->validateSystemLanguage($systemLanguage);

        $this->languages->put($languageId,[
            'id' => $languageId,
            'name'=>$name,
            'default'=>$default,
            'framework_language'=>$systemLanguage
        ]);
        return $this;
    }

    protected function validateSystemLanguage($id)
    {
        $supportedLanguages = ['en'];
        if(!in_array($id, $supportedLanguages)){
            throw new \Exception('Framework language '.$id.' is not supported.');
        }
    }

    public function toArray()
    {
        return [
            'languages'=>$this->languages->toArray(),
            'default_language'=>$this->getDefaultLanguage(),
            'texts'=>collect($this->textBag)->map(function($text){
                return $text->build();
            })->toArray()
        ];
    }
}

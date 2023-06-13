<?php

namespace Surveyforge\Surveyforge\Definitions\Text;

use Illuminate\Support\Str;
use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

class Text extends AbstractBuilder
{
    protected $definitionType=DefinitionType::TEXT;
    protected $id;
    protected $textBag;
    protected $supportedLanguageIds;

    public function __construct($supportedLanguageIds)
    {
        parent::__construct();
        $this->id=Str::orderedUuid()->toString();
        $this->supportedLanguageIds=collect($supportedLanguageIds)->toArray();
        $this->textBag=[];
    }

    public function getId()
    {
        return $this->id;
    }

    public function render(): string
    {
        return "@text_{$this->id}";
    }

    public function translate($languageId,$text)
    {
        if(!in_array($languageId,$this->supportedLanguageIds)){
            throw new \Exception('Language id '.$languageId.' is not supported');
        }

        $this->textBag[$languageId]=$text;
        return $this;
    }

    public function toArray()
    {
        return [
            'text_id'=>$this->id,
            'text'=>$this->textBag
        ];
    }
}

<?php

namespace Surveyforge\Surveyforge\Definitions;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Content\AbstractContent;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;
use Surveyforge\Surveyforge\Definitions\Theme\Theme;

class Survey extends AbstractBuilder
{
    protected $definitionType=DefinitionType::SURVEY;

    protected $title='Untitled Survey';

    protected $orientations;
    protected $endings;
    protected $sections;
    protected $terms;

    protected ?Theme $theme=null;


    public function __construct()
    {
        parent::__construct();
        $this->orientations=collect();
        $this->endings=collect();
        $this->sections=collect();
        $this->terms=collect();
        $this->otherLogos=collect();
    }

    public function withTitle($title)
    {
        $this->title=$title;
        return $this;
    }

    public function addOrientation(AbstractContent $content)
    {
        $this->orientations->add($content);
        return $this;
    }
    public function addEnding(AbstractContent $content)
    {
        $this->endings->add($content);
        return $this;
    }

    public function addToTerms(AbstractContent $content)
    {
        $this->terms->add($content);
        return $this;
    }

    public function addSection(Section $section)
    {
        $this->sections->add($section);
        return $this;
    }

    public function setTheme(Theme $theme)
    {
        $this->theme=$theme;
        return $this;
    }

    public function toArray()
    {
        $this->fillDefaults();

        $definition=[
            'title'=>$this->title,
            'sections'=>$this->sections->map(function(Section $section){
               return $section->build();
            })->toArray(),
            'orientations'=>$this->orientations->map(function(AbstractContent $content){
                return $content->build();
            })->toArray(),
            'terms'=>$this->terms->map(function(AbstractContent $content){
                return $content->build();
            })->toArray(),
            'endings'=>$this->endings->map(function(AbstractContent $content){
                return $content->build();
            })->toArray(),
            'theme'=>$this->theme->build()
        ];

        return $definition;
    }

    protected function fillDefaults()
    {
        if(!$this->theme){
            $this->theme=Theme::getDefault();
        }
    }

}

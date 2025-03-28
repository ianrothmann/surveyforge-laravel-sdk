<?php

namespace Surveyforge\Surveyforge\Definitions;

use Illuminate\Support\Arr;
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
    protected $translator;

    protected ?Theme $theme=null;

    protected $activityProctoringEnabled = false;
    protected $activityMonitoringEnabled = true;

    protected $timeLimitSeconds = null;


    public function __construct()
    {
        parent::__construct();
        $this->orientations=collect();
        $this->endings=collect();
        $this->sections=collect();
        $this->terms=collect();
        $this->otherLogos=collect();
        $this->languages=collect();
    }

    public function withTitle($title)
    {
        $this->title=$this->renderText($title);
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

    public function withTranslator($translator)
    {
        $this->translator=$translator;
        return $this;
    }

    public function setTimeLimit($timeLimitInSeconds)
    {
        if($timeLimitInSeconds && (!is_int($timeLimitInSeconds) || $timeLimitInSeconds<30)){
            throw new \InvalidArgumentException('Time limit must be an integer in seconds larger than 30');
        }
        $this->timeLimitSeconds = $timeLimitInSeconds;
        return $this;
    }

    public function enableActivityProctoring()
    {
        $this->activityProctoringEnabled = true;
        return $this;
    }

    public function enableActivityMonitoring()
    {
        $this->activityMonitoringEnabled = true;
        return $this;
    }

    public function toArray()
    {
        $this->fillDefaults();
        $text=$this->translator ? $this->translator->build() : null;
        $languages=[];

        if($text){
            $languages=Arr::get($text,'languages',[]);
            unset($text['languages']);
        }

        $definition=[
            'version' => '1.0',
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
            'theme'=>$this->theme->build(),
            'languages'=>$languages,
            'text'=> $text,
            'options'=> [
                'activity_proctoring_enabled' => $this->activityProctoringEnabled,
                'activity_monitoring_enabled' => $this->activityMonitoringEnabled,
                'time_limit_seconds' => $this->timeLimitSeconds
            ]
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

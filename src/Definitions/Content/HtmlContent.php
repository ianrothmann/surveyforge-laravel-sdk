<?php

namespace Surveyforge\Surveyforge\Definitions\Content;

class HtmlContent extends AbstractContent
{
    protected string $type = self::HTML;

    protected $title;
    protected $html;

    public function __construct($html=null)
    {
        parent::__construct();
        $this->withHtml($html);
    }

    public function withHtml($html)
    {
        $this->html=$this->renderText($html);
        return $this;
    }

    public function withTitle($title)
    {
        $this->title=$this->renderText($title);
        return $this;
    }

    public function toArray()
    {
        $definition=parent::toArray();
        $definition['title']=$this->title;
        $definition['html']=$this->html;
        return $definition;
    }
}

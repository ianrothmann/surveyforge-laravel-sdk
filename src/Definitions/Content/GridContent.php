<?php

namespace Surveyforge\Surveyforge\Definitions\Content;

class GridContent extends AbstractContent
{
    protected string $type = self::GRID;

    protected $gridElements;
    protected $title;

    protected int $gridColumns=1;

    public function __construct()
    {
        parent::__construct();
        $this->gridElements=collect();
    }

    public function numberOfGridColumns($numberOfColumns)
    {
        $this->gridColumns=$numberOfColumns;
        return $this;
    }

    public function withTitle($title)
    {
        $this->title=$this->renderText($title);
        return $this;
    }

    public function addContent(AbstractContent $content, $colSpan=1, $rowSpan=1)
    {
        $this->gridElements[] = [
            'content'=>$content,
            'colSpan'=>$colSpan,
            'rowSpan'=>$rowSpan
        ];
        return $this;
    }

    public function addText($text, $colSpan=1, $rowSpan=1)
    {
        $this->gridElements[] = [
            'text'=>$text,
            'colSpan'=>$colSpan,
            'rowSpan'=>$rowSpan
        ];
        return $this;
    }

    public function addHtml($html, $colSpan=1, $rowSpan=1)
    {
        $this->gridElements[] = [
            'content'=>(new HtmlContent())->withHtml($html),
            'colSpan'=>$colSpan,
            'rowSpan'=>$rowSpan
        ];
        return $this;
    }

    public function toArray()
    {
        $definition=parent::toArray();
        $definition['title']=$this->title;
        $definition['elements']=$this->gridElements->map(function($def){
            if(array_key_exists('content',$def)){
                $def['content']=$def['content']->build();
            }
            return $def;
        });
        return $definition;
    }
}

<?php

namespace Surveyforge\Surveyforge\Definitions\Theme;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

class Theme extends AbstractBuilder
{
    protected $definitionType=DefinitionType::THEME;

    protected string $primary;
    protected string $secondary;
    protected string $accent;
    protected string $info;
    protected string $error;
    protected string $warning;
    protected string $success;

    protected ?string $logoOnLightUrl;
    protected ?string $logoOnDarkUrl=null;

    protected ?string $iconOnLightUrl=null;
    protected ?string $iconOnDarkUrl=null;

    protected $otherLogos;

    public function __construct()
    {
        parent::__construct();
        $this->otherLogos=collect();
    }

    public function withPrimaryColor($color)
    {
        $this->primary=$color;
        return $this;
    }

    public function withSecondaryColor($color)
    {
        $this->secondary=$color;
        return $this;
    }

    public function withAccentColor($color)
    {
        $this->accent=$color;
        return $this;
    }

    public function withInfoColor($color)
    {
        $this->info=$color;
        return $this;
    }

    public function withErrorColor($color)
    {
        $this->error=$color;
        return $this;
    }

    public function withWarningColor($color)
    {
        $this->warning=$color;
        return $this;
    }

    public function withSuccessColor($color)
    {
        $this->success=$color;
        return $this;
    }

    public function setIconUrl($iconOnLightUrl, $iconOnDarkUrl=null)
    {
        $this->iconOnDarkUrl=$iconOnDarkUrl;
        $this->iconOnLightUrl=$iconOnLightUrl;
        return $this;
    }

    public function setLogoUrl($iconOnLightUrl, $iconOnDarkUrl=null)
    {
        $this->logoOnDarkUrl=$iconOnDarkUrl;
        $this->logoOnLightUrl=$iconOnLightUrl;
        return $this;
    }

    public function addOtherLogo($logoUrl)
    {
        $this->otherLogos->add($logoUrl);
        return $this;
    }

    public function toArray()
    {
        return [
            'colors'=>[
                'primary'=>$this->primary,
                'secondary'=>$this->secondary,
                'accent'=>$this->accent,
                'info'=>$this->info,
                'error'=>$this->error,
                'warning'=>$this->warning,
                'success'=>$this->success,
            ],
            'logo'=>[
                'on_light'=>$this->logoOnLightUrl,
                'on_dark'=>$this->logoOnDarkUrl,
            ],
            'icon'=>[
                'on_light'=>$this->iconOnLightUrl,
                'on_dark'=>$this->iconOnDarkUrl,
            ],
            'other_logos'=>$this->otherLogos->toArray()
        ];
    }

    public static function getDefault()
    {
        return (new Theme())
            ->withPrimaryColor('#4f46e5')
            ->withSecondaryColor('#4f46e5')
            ->withAccentColor('#4f46e5')
            ->withInfoColor('#4f46e5')
            ->withSuccessColor('#8c9eff')
            ->withWarningColor('#FF8D11')
            ->withErrorColor('#b71c1c')
            ->setLogoUrl(null,'https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=300')
            ->setIconUrl(null,'https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=300');
    }

}

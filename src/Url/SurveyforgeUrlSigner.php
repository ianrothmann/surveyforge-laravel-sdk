<?php

namespace Surveyforge\Surveyforge\Url;

use Illuminate\Support\Carbon;

class SurveyforgeUrlSigner
{
    protected $url;
    protected $secret;
    protected $expiresAt;

    public function __construct($secret)
    {
        $this->secret = $secret;
        $this->expiresAt=now()
            ->addMinutes(60)
            ->timestamp;
    }

    public static function withSecret($secret)
    {
        return new static($secret);
    }

    public function expiresAt(Carbon $expiresAt)
    {
        $this->expiresAt=$expiresAt->timestamp;
        return $this;
    }

    public function sign($url)
    {
        return $this->addSignature($url);
    }

    public function check($url)
    {
        $parts=parse_url($url);
        $host=$parts['host'] ?? '';
        $path=$parts['path'] ?? '';
        $query_parts=[];
        parse_str($parts['query'] ?? '', $query_parts);
        $signature=$query_parts['signature'];
        unset($query_parts['signature']);
        return $signature===$this->getSignature($host, $path, $query_parts);
    }

    protected function addSignature($url)
    {
        $parts=parse_url($url);
        $host=$parts['host'] ?? '';
        $path=$parts['path'] ?? '';
        $query_parts=[];
        parse_str($parts['query'] ?? '', $query_parts);
        $query_parts['expires']=$this->expiresAt;
        $query_parts['signature']=$this->getSignature($host, $path, $query_parts);
        $parts['query']=http_build_query($query_parts);
        return $this->buildUrl($parts);
    }

    protected function buildUrl(array $parts)
    {
        return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') .
            ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') .
            (isset($parts['user']) ? "{$parts['user']}" : '') .
            (isset($parts['pass']) ? ":{$parts['pass']}" : '') .
            (isset($parts['user']) ? '@' : '') .
            (isset($parts['host']) ? "{$parts['host']}" : '') .
            (isset($parts['port']) ? ":{$parts['port']}" : '') .
            (isset($parts['path']) ? "{$parts['path']}" : '') .
            (isset($parts['query']) ? "?{$parts['query']}" : '') .
            (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
    }

    public function getSignature($host,$path, $queryParts=[])
    {
        ksort($queryParts);
        $query=http_build_query($queryParts);
        $signature=md5($host.'::'.$path.'::'.$query.'::'.$this->secret);
        return $signature;
    }
}

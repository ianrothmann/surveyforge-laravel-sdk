<?php

namespace Surveyforge\Surveyforge\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Surveyforge\Surveyforge\Surveyforge
 */
class Surveyforge extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Surveyforge\Surveyforge\Surveyforge::class;
    }
}

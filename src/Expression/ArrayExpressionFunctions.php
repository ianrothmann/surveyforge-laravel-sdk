<?php


namespace Surveyforge\Surveyforge\Expression;


class ArrayExpressionFunctions
{
    public function in_array()
    {
        $vars=collect(func_get_args());
        $haystack=$vars->get(1);
        $needle=$vars->get(0);
        return collect($haystack)->contains($needle);
    }

}

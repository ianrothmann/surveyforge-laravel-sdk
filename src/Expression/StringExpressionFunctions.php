<?php

namespace Surveyforge\Surveyforge\Expression;

class StringExpressionFunctions
{

    public function equals($val1,$val2)
    {
        return $val1==$val2 ? "1" : "0";
    }

    public function is_null($str)
    {
        return $str===null;
    }

}

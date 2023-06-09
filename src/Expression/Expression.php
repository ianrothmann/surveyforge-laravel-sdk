<?php

namespace Surveyforge\Surveyforge\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Expression
{
    protected $variables,$expression;

    public function __construct($expression)
    {
        $this->variables=collect();
        $this->expression=$expression;
    }

    public static function init($expression)
    {
        return new self($expression);
    }

    public function withVariables($variables)
    {
        $this->variables=$this->variables->union($variables);
        return $this;
    }

    public function evaluate()
    {
        $expressionLanguage=new ExpressionLanguage();
        $this->registerFunctions($expressionLanguage);
        return $expressionLanguage->evaluate($this->expression,$this->variables->toArray());
    }

    protected function registerFunctions(ExpressionLanguage $expressionLanguage)
    {
        $expressionLanguage->addFunction(ExpressionFunction::fromPhp('in_array'));
        $expressionLanguage->addFunction(ExpressionFunction::fromPhp('is_null'));

        $expressionLanguage->register('in_array', function ($str) {
            return sprintf('%1$s', $str);
        }, function ($data,$needle,$arr) {
            if (!is_array($needle)) {
                $needle=[$needle];
            }
            if (!is_array($arr)) {
                $arr=[$arr];
            }
            return sizeof(array_intersect($needle,$arr))>0;
        });
    }
}

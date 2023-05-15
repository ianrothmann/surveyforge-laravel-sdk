<?php

namespace Surveyforge\Surveyforge\Definitions\Condition;

use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;

class Condition extends AbstractBuilder
{
    protected $queryDefinition=[];
    protected $runInverse=false;
    protected $columns;
    protected $definitionType=DefinitionType::CONDITION;

    public function __construct($runInverse=false)
    {
        parent::__construct();
        $this->runInverse=$runInverse;
        $this->columns=collect();
    }

    public function where($column,$operatorOrValueOrFunction=null,$value=null)
    {
        $this->processWhereClause('AND',$column,$operatorOrValueOrFunction,$value);
        return $this;
    }

    public function orWhere($column,$operatorOrValueOrFunction=null,$value=null)
    {
        $this->processWhereClause('OR',$column,$operatorOrValueOrFunction,$value);
        return $this;
    }

    public function whereIn($column,$values=[])
    {
        $this->processWhereClause('AND',$column,'IN',$values);
        return $this;
    }

    public function orWhereIn($column,$values=[])
    {
        $this->processWhereClause('OR',$column,'IN',$values);
        return $this;
    }

    public function whereNull($column)
    {
        $this->processWhereClause('AND',$column,'IS','NULL');
        return $this;
    }

    public function orWhereNull($column)
    {
        $this->processWhereClause('OR',$column,'IS','NULL');
        return $this;
    }

    protected function processWhereClause($boolean,$column,$operatorOrValueOrFunction,$value=null)
    {
        if($value===null && !$column instanceof \Closure)
        {
            $this->addColumn($column);
            $value=$operatorOrValueOrFunction;
            $operatorOrValueOrFunction="=";
            $queryDefinition=[
                'boolean'=>$boolean,
                'column'=>$column,
                'operator'=>$operatorOrValueOrFunction,
                'value'=>$value
            ];
        }elseif($value!==null && !$column instanceof \Closure)
        {
            $this->addColumn($column);
            $queryDefinition=[
                'boolean'=>$boolean,
                'column'=>$column,
                'operator'=>$operatorOrValueOrFunction,
                'value'=>$value
            ];
        }elseif ($column instanceof \Closure) {
            $ref=new self();
            $column($ref);

            $queryDefinition = [
                'boolean' => $boolean,
                'clause' => $ref->getQueryGrammar()
            ];
        }

        $this->queryDefinition[]=$queryDefinition;

        return $queryDefinition;
    }

    protected function addColumn($column)
    {
        $this->columns->push($column);
    }

    public function getQueryGrammar()
    {
        return $this->queryDefinition;
    }

    public function toArray()
    {
        return [
            'run_inverse'=>$this->runInverse,
            'columns'=>$this->columns->toArray(),
            'query'=>$this->queryDefinition
        ];
    }
}

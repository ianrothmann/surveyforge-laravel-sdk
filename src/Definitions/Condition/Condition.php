<?php

namespace Surveyforge\Surveyforge\Definitions\Condition;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Surveyforge\Surveyforge\Definitions\Builders\AbstractBuilder;
use Surveyforge\Surveyforge\Definitions\Interfaces\DefinitionType;
use Surveyforge\Surveyforge\Utils\ArrayUtils;

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

    public function whereNot($column,$operatorOrValueOrFunction=null,$value=null)
    {
        $this->processWhereClause('AND',$column,$operatorOrValueOrFunction,$value,true);
        return $this;
    }

    public function orWhereNot($column,$operatorOrValueOrFunction=null,$value=null)
    {
        $this->processWhereClause('OR',$column,$operatorOrValueOrFunction,$value,true);
        return $this;
    }

    public function whereIn($column,$values=[])
    {
        $this->processWhereClause('AND',$column,'IN',$values);
        return $this;
    }

    public function whereNotIn($column,$values=[])
    {
        $this->processWhereClause('AND',$column,'IN',$values,true);
        return $this;
    }

    public function orWhereIn($column,$values=[])
    {
        $this->processWhereClause('OR',$column,'IN',$values);
        return $this;
    }

    public function orWhereNotIn($column,$values=[])
    {
        $this->processWhereClause('OR',$column,'IN',$values,true);
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

    public function whereNotNull($column)
    {
        $this->processWhereClause('AND',$column,'IS','NULL',true);
        return $this;
    }

    public function orWhereNotNull($column)
    {
        $this->processWhereClause('OR',$column,'IS','NULL',true);
        return $this;
    }

    protected function processWhereClause($boolean,$column,$operatorOrValueOrFunction,$value=null,$not=false)
    {
        if($value===null && !$column instanceof \Closure)
        {
            $column=$this->inferFullColumnName($column);
            $this->addColumn($column);
            $value=$operatorOrValueOrFunction;
            $operatorOrValueOrFunction="=";
            $queryDefinition=[
                'boolean'=>$boolean,
                'column'=>$column,
                'operator'=>$operatorOrValueOrFunction,
                'value'=>$value,
                'not'=>$not
            ];
        }elseif($value!==null && !$column instanceof \Closure)
        {
            $column=$this->inferFullColumnName($column);
            $this->addColumn($column);
            $queryDefinition=[
                'boolean'=>$boolean,
                'column'=>$column,
                'operator'=>$operatorOrValueOrFunction,
                'value'=>$value,
                'not'=>$not
            ];
        }elseif ($column instanceof \Closure) {
            $ref=new self();
            $column($ref);

            $queryDefinition = [
                'boolean' => $boolean,
                'not' => $not,
                'clause' => $ref->getQueryGrammar()
            ];

            $this->addColumn($ref->columns->toArray());
        }

        $this->queryDefinition[]=$queryDefinition;

        return $queryDefinition;
    }

    /**
     * This function checks if dot notation was used. If not, it will add the question Id to the column Id, assuming they were the same.
     * @param $column
     * @return void
     */
    protected function inferFullColumnName($column)
    {
        if(!Str::contains($column,'.'))
        {
            $column=$column.'.'.$column;
        }

        return $column;
    }

    protected function addColumn($columnOrArr)
    {
        if(is_array($columnOrArr)) {
            foreach ($columnOrArr as $column) {
                $this->columns->push($column);
            }
        }else{
            $this->columns->push($columnOrArr);
        }
        $this->columns=$this->columns->unique();
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
            'query'=>$this->queryDefinition,
            'syntax'=>$this->parseConditionSyntax()
        ];
    }

    protected function parseConditionSyntax()
    {
        $definition=$this->queryDefinition;

        $condition='[|_start_delimiter_|]'.$this->parseConditionArray($definition);

        //Finally check if it should run inverse
        if($this->runInverse && $condition!=''){
            $condition="!({$condition})";
        }

        $condition=Str::replace(['[|_start_delimiter_|] && ','[|_start_delimiter_|] || '],'',$condition);
        return $condition;
    }

    protected function parseConditionArray($arr)
    {
        if(isset($arr['clause'])){
            $clauses=[];
            foreach ($arr['clause'] as $clause){
                $clauses[]=$this->parseConditionArray($clause);
            }
            $stringClauses=implode('',$clauses);
            $not=$arr['not']?'!':'';
            if($arr['boolean']=='AND' && sizeof($clauses) > 0) {
                $stringClauses = " && {$not}([|_start_delimiter_|]{$stringClauses})";
            }else{
                $stringClauses = " || {$not}([|_start_delimiter_|]{$stringClauses})";
            }
            return $stringClauses;
        }elseif(isset($arr['boolean'])){
            return $this->convertLogicArrayToSyntax($arr);
        }elseif(is_array($arr)){
            $clauses=[];
            foreach ($arr as $clause){
                $clauses[]=$this->parseConditionArray($clause);
            }
            return implode('',$clauses);
        }
    }

    protected function convertLogicArrayToSyntax($logic)
    {
        if(array_key_exists('clause',$logic)){
            throw new \Exception('Cannot convert logic array to syntax, because it is a subclause');
        }

        $logic=collect($logic);
        $clauseOperator=$logic->get('boolean','AND');
        $operator=$logic->get('operator');
        $column=$logic->get('column');
        $value=$logic->get('value');
        $not=$logic->get('not');
        if(is_array($value)){
            $value=implode(',',$value);
        }

        if($operator=='IS' && $value=='NULL'){
            $syntax="is_null(`$column`)";
        }elseif($operator=='IN'){
            $syntax="in_array(`$column`,[$value])";
        }else{
            if($operator=='='){
                $operator='==';
            }

            $syntax="`$column` $operator $value";
        }

        $not=$not?'!':'';
        if($clauseOperator=='AND'){
            $syntax=" && {$not}({$syntax})";
        }elseif ($clauseOperator=='OR'){
            $syntax=" || {$not}({$syntax})";
        }

        return $syntax;
    }
}

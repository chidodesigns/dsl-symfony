<?php
namespace App\Services;

use ArrayAccess;

class QueryDataChecker implements ArrayAccess
{

    private array $expression = [];

    public function __construct(array $expression)
    {
        $this->expression = $expression;   
    }

    protected function getArrayExpression() 
    {
        return $this->expression;
    }

    public function offsetExists($offset): bool
    {
        // return array_key_exists($offset, $this->expression);
        return isset($this->expression[$offset]);
        
    }

    public function offsetSet($offset, $value): void
    {
        if($offset){
            $this->expression[$offset] = $value;
        }else{
            $this->expression[] = $value;
        }
       
    }

    public function offsetGet($offset)
    {
        return $this->expression[$offset] ?? null;
    }


    public function offsetUnset($offset): void
    {
        
    }


    protected function count(array $dslArray, int $arrayCount ): int
    {
        $count = count($dslArray);
        if($count !== $arrayCount){
            return false;
        }
        return true;

    }

    
}
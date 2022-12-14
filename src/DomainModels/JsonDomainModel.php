<?php 
namespace App\DomainModels;

use App\DomainModels\DomainModel;

class JsonDomainModel extends DomainModel
{
    protected $security;
    protected $expression; 
    private string $fn;
    private $a;
    private $b;


    public function __construct( string $security, array $expression)
    {
        parent::__construct($security, $expression);

        $this->security = $security;
        $this->expression = $expression;
        $this->fn = $this->expression['fn'];
        $this->a = $this->expression['a'];
        $this->b = $this->expression['b'];
    }

    // N.B - Added This Fn - resides on its parent but tests were failing
    public function getSecurity():string
    {
        return $this->security;
    }

    // N.B - Added This Fn - resides on its parent but tests were failing
    public function getExpression(): array
    {
        return $this->expression;
    }

    public function getFn():string
    {
        return $this->fn;
    }

    public function getArgA()
    {
        return $this->a;
    }

    public function getArgB()
    {
        return $this->b;
    }

    public function write():array
    {
        return [
            'security' => $this->security,
            'expression_result' => $this->expression
        ];
    }

}
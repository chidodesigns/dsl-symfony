<?php
namespace App\DomainModels;

abstract class DomainModel
{

    protected $security;
    protected $expression;

    public function __construct(string $security, array $expression)
    {
        $this->security = $security;
        $this->expression = $expression;
    }

    public function setSecurity(string $security):self
    {
        $this->security = $security;

        return $this;
    }

    public function getSecurity(): string
    {
        return $this->security;
    }

    public function setExpression(array $expression):self
    {
        $this->expression = $expression;

        return $this;
    }

    public function getExpression(): array
    {
        return $this->expression;
    }

    abstract public function write():array;

 
}

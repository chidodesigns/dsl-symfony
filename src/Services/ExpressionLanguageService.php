<?php
namespace App\Services;


use Symfony\Component\ExpressionLanguage\ExpressionLanguage;


class ExpressionLanguageService
{

    private $expressionLanguage;

    public function __construct()
    {
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function evaluateExpression (string $expression) :string
    {
        return $this->expressionLanguage->evaluate($expression);
    }
   

}
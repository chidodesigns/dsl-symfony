<?php

namespace App\DomainModels;

use App\Exception\CustomBadRequestHttpException;
use App\Traits\AttributeManagerTrait;
use App\Traits\FactManagerTrait;
use App\Traits\SecurityManagerTrait;

class StockpediaBasicModel
{
    private $security;
    private $fn;

    private $factValue;

    private $argAttribute;
    private $argNumber;
    private $argExpression;

    use FactManagerTrait;
    use SecurityManagerTrait;
    use AttributeManagerTrait;

    public function dslQueryBuilder($query){

        //  Basic Query Ruling

        //  First 3 Levels MUST be set

        //////// FIRST LEVEL - security + expression //////

        // Rule [verb]
        $securitySymbol = $this->checkDslQueryFormat($query, "security");

        if ($securitySymbol) {
            $this->security = $this->securityManager->findSecuritySymbol($query['security']);
        }

        //  Format Check [Noun]
        $expression = $this->checkDslQueryFormat($query, "expression");

        //////// SECOND LEVEL - operator //////

        $expressionOperator = $this->checkDslQueryFormat($query['expression'], "operator");

        // Third Level - fn + arguments

        // Rule [verb]
        $expressionOperatorFn = $this->checkDslQueryFormat($query['expression']['operator'], "fn");

        $expressionOperatorFnExists = $this->checkOperatorFn($query['expression']['operator']["fn"]) ? $query['expression']['operator']["fn"] : false;


        //  Format Check [Noun]
        $expressionOperationArguments = $this->checkDslQueryFormat($query['expression']['operator'], "arguments");


        ////// FOURTH LEVEL - attribute + number + expression //////

        $expressionOperationArgumentsAttribute = isset($query['expression']['operator']['arguments']['attribute']) ? $query['expression']['operator']['arguments']['attribute'] : false;

        if ($expressionOperationArgumentsAttribute) {
            $this->argAttribute = $this->attributeManager->findAttributeName($expressionOperationArgumentsAttribute);
        }

        $this->argNumber = isset($query['expression']['operator']['arguments']['number']) ? $query['expression']['operator']['arguments']['number'] : false;

        if ($this->argNumber) {

            if (!is_numeric($this->argNumber)) {
                throw new CustomBadRequestHttpException([
                    'status' => 0,
                    'errorCode' => 'QUERYF100',
                    'errorMessage' => 'Invalid Query Format: argument number must be an integer'
                ], 400);
            }
        }

        $this->argExpression = isset($query['expression']['operator']['arguments']['expression']) ? $query['expression']['operator']['arguments']['expression'] : false;

        $doesExpArgsExist = $this->checkDslExpressionArgs($expressionOperationArgumentsAttribute,  $this->argNumber, $this->argExpression);

        // Now Search For Security Fact
        $this->factValue = $this->factManager->selectFact($this->argAttribute, $this->security);

        // Finish Off Stockedpedia Search Query
        $result = $this->returnOperatorMethod($expressionOperatorFnExists);

        return $result;
    
    }

    public function checkDslQueryFormat($dslArray, string $dslArrayTtitle)
    {
        if (!isset($dslArray)) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format:' . $dslArrayTtitle . ' required'
            ], 400);
        }
        return array_key_exists($dslArrayTtitle, $dslArray);
    }


    // Check Fn Operator
    public function checkOperatorFn(string $fn)
    {
        $operators = ["+", "-",  "*", "/"];
        if (in_array($fn, $operators)) {
            return true;
        } else {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: operator fn not valid'
            ], 400);
        }
    }

    public function checkDslExpressionArgs($attribute, $number, $expression)
    {
        //  @TODO needs a refactor to check if 1 expression arg has been sent
        if (!$attribute && !$number && !$expression) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: arguments cannot be null'
            ], 400);
        } elseif ($attribute && $number && $expression) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: only 2 arguments allowed'
            ], 400);
        } else {
            return true;
        }
    }

    public function returnOperatorMethod(string $fn)
    {
        switch ($fn) {
            case "+":
                return $this->argNumber + $this->factValue;

                break;
            case "-":
                return 'SUBTRACT';
                break;
            case "*":
                return 'MULTIPLY';
                break;
            case "/":
                return 'DIVIDE';
                break;
            default:
                echo 'Finished';
        }
    }

 
}

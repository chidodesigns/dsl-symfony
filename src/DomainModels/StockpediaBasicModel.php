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

    public function dslQueryBuilder($query)
    {

        /**
         * DSL Query Builder
         * /////////////////
         * SYNOPSIS ---
         * //////////
         * DSL Query Builder Takes A JSON Payload From Controller And Parses Through It.
         * The Query Builder acts as rule validator for our DSL to make sure a user is inputing and adhering to the rules of the DSL.
         * The Query Builder will stop the programming from running when it comes across and error and immeadiately sends an error response back to the client, the expression is not evaluated.
         * The Query Builder also analyzes the incoming expression and sends a response back to the client.
         * ////////////
         * HOW IT WORKS ---
         * The Query Builder recieves a json_decoded payload and breaks down the incoming payload expression into four layers
         * Layer 1: Analyzes the key titles that make up a valid query.
         * Layer 2: Analyzes the key titles that make up a valid expression
         * Layer 3: Analyzes the key titles that make up a valid operator
         * Layer 4: Analyzes the key titles that make up a valid argument structure.
         * ////////////
         * DSL SYNTAX (KEYWORDS) ---
         * keyword: 'security': [Entity Model]
         * keyword: 'expression'[Query Formatter] 
         * keyword: 'operator' [Query Formatter]
         * keyword: 'fn' [Triggers Function]
         * keyword: 'arguments' [Query Formatter]
         * keyword: 'attribute': [Entity Model]
         * keyword: 'number" [Action Var]
         * ///////////
         * 
         */

        
        //////// FIRST LEVEL - security + expression //////

        //  [ENTITY MODEL] : keyword: security
        $securitySymbol = $this->checkDslQueryFormat($query, "security");

        //  [QUERY FORMATTER] : keyword: expression
        $expression = $this->checkDslQueryFormat($query, "expression");

        //////// SECOND LEVEL - operator //////

        //  [QUERY FORMATTER] :keyword: operator
        $expressionOperator = $this->checkDslQueryFormat($query['expression'], "operator");

        /////// THIRD LEVEL  - fn + arguments //////
        
        //  [TRIGGERS FUNCTION] : keyword: fn
        $expressionOperatorFn = $this->checkDslQueryFormat($query['expression']['operator'], "fn");

        //  [QUERY FORMATTER] : keyword : arguments :
        $expressionOperationArguments = $this->checkDslQueryFormat($query['expression']['operator'], "arguments");

        ////// FOURTH LEVEL - attribute + number + expression //////

        //  [ENTITY MODEL] : keyword : attribute 
        $expressionOperationArgumentsAttribute = isset($query['expression']['operator']['arguments']['attribute']) ? $query['expression']['operator']['arguments']['attribute'] : false;

        //  [ACTION VAR] : keyword : number:
        $this->argNumber = isset($query['expression']['operator']['arguments']['number']) ? $query['expression']['operator']['arguments']['number'] : false;

        //  [QUERY FORMATTER] : keyword: expression
        $this->argExpression = isset($query['expression']['operator']['arguments']['expression']) ? $query['expression']['operator']['arguments']['expression'] : false;
        
  
        //  Further Query Processing 

        // Check the [triggers function] :keyword: fn:  operator exists
        $expressionOperatorFnExists = $this->checkOperatorFn($query['expression']['operator']["fn"]) ? $query['expression']['operator']["fn"] : false;

        //  Analyse count within this [query formatter] :keyword: arguments array
        if ($expressionOperationArguments) {
            //  Check Operation Arguments 
            $expressionOperationArgumentsLegit = $this->checkOperatorArguments($query['expression']['operator']['arguments']);
        }

        //  Analyse data set within [query formatter] :keyword: arguments
        $doesExpArgsExist = $this->checkDslExpressionArgs($expressionOperationArgumentsAttribute,  $this->argNumber, $this->argExpression);
        
        //  Check this [action var] :keyword: number: is numerical
        if ($this->argNumber) {

            if (!is_numeric($this->argNumber)) {
                throw new CustomBadRequestHttpException([
                    'status' => 0,
                    'errorCode' => 'QUERYF100',
                    'errorMessage' => 'Invalid Query Format: argument number must be an integer'
                ], 400);
            }
        }

        //  Database Actions

        //  Check [entity model] :keyword: security exists
         if ($securitySymbol) {
            $this->security = $this->securityManager->findSecuritySymbol($query['security']);
        }


        //  Check [entity model] :keyword: attribute exists
        if ($expressionOperationArgumentsAttribute) {
            $this->argAttribute = $this->attributeManager->findAttributeName($expressionOperationArgumentsAttribute);
        }

        //  Search within the Fact Collection to find corresponding attribute && security
        $this->factValue = $this->factManager->selectFact($this->argAttribute, $this->security);

        //  Look for fn to trigger a function call
        $result = $this->returnOperatorMethod($expressionOperatorFnExists);

        return $result;
    }

    public function checkDslQueryFormat($dslArray, string $dslArraytitle)
    {
        if (!isset($dslArray)) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format:' . $dslArraytitle . ' required'
            ], 400);
        }
        return array_key_exists($dslArraytitle, $dslArray);
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

    //  Check Arguments Rule
    public function checkOperatorArguments(array $operatorArgs)
    {
        if (!is_array($operatorArgs)) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: arguments must be an array object'
            ], 400);
        }
        $count = count($operatorArgs);

        if ($count !== 2) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: You must pass 2 operator arguments to query'
            ], 400);
        }

        return;
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

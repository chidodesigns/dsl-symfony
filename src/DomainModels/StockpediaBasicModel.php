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
    private $attribute;
    private $number;
    private $expression = array();
    private $factValue;

    use FactManagerTrait;
    use SecurityManagerTrait;
    use AttributeManagerTrait;

    public function dslQueryBuilder($query)
    {

        //  [ENTITY MODEL] : keyword: security
        $securitySymbol = $this->checkDslQueryFormat($query, "security");

        //  [QUERY FORMATTER] : keyword: expression
        $expression = $this->checkDslQueryFormat($query, "expression");

        //  Check Expression Is DSL compliant
        $isExpressionValid = $this->dslArrayChecker($query['expression'], 3);

        //  Check fn title is set
        $expressionFn = $this->checkDslQueryFormat($query['expression'], "fn");

        //  Check Arg $a is set
        $a = $this->checkDslQueryFormat($query['expression'], "a");
        $dataType = $this->dslCheckArgType($query['expression']['a']);
        //  Analyse Arg A
        return;

        //  Check Arg $a is set
        $b = $this->checkDslQueryFormat($query['expression'], "b");

        $doesOperatorExist = $this->checkOperatorFn($query['expression']["fn"]) ? $query['expression']["fn"] : false;

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

        //  Search within the Fact Collection to find corresponding attribute && security
        $this->factValue = $this->factManager->selectFact($this->argAttribute, $this->security);

        // return [
        //     'security' => $query['security'],
        //     // 'attribute' => $expressionOperationArgumentsAttribute,
        //     'operator_fn' => $this->fn,
        //     'operator_arguments' => $query['expression']['operator']['arguments'],
        //     'expression_result' => $result
        // ];
    }

    public function checkDslQueryFormat($dslArray, string $dslArraytitle = '')
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

    public function dslCheckArgType($arg, $isArgAnExpression = false)
    {
        //  Make sure when ArgIsExpression check is does NOT hold another expression
        if ($isArgAnExpression && is_array($arg)) {
            $isArgAnExpression = $this->dslArrayChecker($arg, 3);
            //  If it has an array count 3 - it is deemed as another expression so fail evaulation
            if ($isArgAnExpression) {
                throw new CustomBadRequestHttpException([
                    'status' => 0,
                    'errorCode' => 'QUERYF100',
                    'errorMessage' => 'Invalid Query Format: Cannot pass another an expression within an expression argument'
                ], 400);
            } else {
                throw new CustomBadRequestHttpException([
                    'status' => 0,
                    'errorCode' => 'QUERYF100',
                    'errorMessage' => 'Invalid Query Format'
                ], 400);
            }
        }

        $dataType = gettype($arg);
        switch ($dataType) {
            case "string":
                $this->dslArgIsString($arg);
                //  If the arg is an expression and the first arg is a string return attribute
                if ($isArgAnExpression) {
                    return array_merge($this->attributeManager->findAttributeName($arg), ['data_type' => $dataType]);
                }
                break;
            case "array":
                $this->dslArgIsExpression($arg);
                break;
            case "integer":
                $this->number = $arg;
                if ($isArgAnExpression) {
                    return [
                        'data_type' => $dataType,
                        'value' => $arg
                    ];
                }
                break;
        }
    }

    public function dslArgIsString($arg): array
    {
        $this->attribute = $this->attributeManager->findAttributeName($arg);
        return $this->attribute;
    }

    //  Processing Expression Arguments
    public function dslArgIsExpression($arg)
    {
        $this->checkDslQueryFormat($arg, 'fn');
        $fn = $arg['fn'];

        $operator = $this->checkOperatorFn($fn, true);
        
        $this->checkDslQueryFormat($arg, 'a');

        $a = $this->dslCheckArgType($arg['a'], true);

        $this->checkDslQueryFormat($arg, 'b');

        $b = $this->dslCheckArgType($arg['b'], true);
    }

    public function dslArrayChecker(array $dslArray, int $arrayCount)
    {
        if (!is_array($dslArray)) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format:' . $dslArray . 'must be an array object'
            ], 400);
        }
        $count = count($dslArray);

        if ($count !== $arrayCount) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: ' . $dslArray . 'must have ' . $arrayCount . 'values'
            ], 400);
        }

        return true;
    }

    // Check Fn Operator
    public function checkOperatorFn(string $fn, $isArgAnExpression = false)
    {
        $operators = ["+", "-",  "*", "/"];
        if (in_array($fn, $operators)) {

            if ($isArgAnExpression) {
                return [
                    'operator' => $fn,
                ];
            }

            $this->fn = $fn;
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
                return $this->addQueryParams($this->argNumber, $this->factValue);

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

    public function addQueryParams($a, $b)
    {
        return $a + $b;
    }
}

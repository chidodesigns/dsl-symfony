<?php

namespace App\DomainModels;

use App\Exception\CustomBadRequestHttpException;
use App\Services\ExpressionLanguageService;
use App\Traits\AttributeManagerTrait;
use App\Traits\FactManagerTrait;
use App\Traits\SecurityManagerTrait;

class StockpediaBasicModel
{
    private $security;
    private $fn;
    private $fnType;
    private $attribute = null;
    private $number = null;
    private $factValue;

    /** @var ExpressionLanguageService $expressionLanguageService */
    private $expressionLanguageService;

    use FactManagerTrait;
    use SecurityManagerTrait;
    use AttributeManagerTrait;

    public function __construct(ExpressionLanguageService $expressionLanguageService)
    {
        $this->expressionLanguageService = $expressionLanguageService;
    }

    public function dslQueryBuilder($query)
    {

        //  [ENTITY MODEL] : keyword: security
        $securitySymbol = $this->checkDslQueryFormat($query, "security");

        if ($securitySymbol) {
            $this->security = $this->securityManager->findSecuritySymbol($query['security']);
        }

        //  [QUERY FORMATTER] : keyword: expression
        $expression = $this->checkDslQueryFormat($query, "expression");

        //  Check Expression Is DSL compliant
        $isExpressionValid = $this->dslArrayChecker($query['expression'], 3);

        //  Check fn title is set
        $expressionFn = $this->checkDslQueryFormat($query['expression'], "fn");
        //  Set the $this->fn class prop
        $this->checkOperatorFn($query['expression']["fn"]) ? $query['expression']["fn"] : false;

        //  Check Arg $a is set
        $a = $this->checkDslQueryFormat($query['expression'], "a");
        //  Returns result for this param
        $aArgResult = $this->dslCheckArgType($query['expression']['a']);

        //  Check Arg $a is set
        $b = $this->checkDslQueryFormat($query['expression'], "b");
        $bArgResult = $this->dslCheckArgType($query['expression']['b']);

        //  Handles - When both arg expressions are NOT expressions
        if ($this->attribute !== null && $this->number !== null) {
            //  Search within the Fact Collection to find corresponding attribute && security
            $this->factValue = $this->factManager->selectFact($this->attribute['attr_id'], $this->security);

            // if fn is an subtract operator arg b is the first value in expression according to operator behaviour arguments
            if($this->fnType == 'subtract'){

                if($aArgResult['data_type'] == 'integer'){
                    $expression = $this->factValue . $this->fn . $this->number;
                }elseif($bArgResult['data_type'] == 'integer'){
                    $expression = $this->number . $this->fn . $this->factValue;
                }

            }else{
                $expression = $this->factValue . $this->fn . $this->number;
            }

            $evaluatedExpression = $this->expressionLanguageService->evaluateExpression($expression);
            return [
                'query' => $query,
                'expression_result' => $evaluatedExpression
            ];
        }

        // if($this->attribute !== null && $this->number == null && $aArgResult !== null && $bArgResult == null){
        //     $this->factValue = $this->factManager->selectFact($this->attribute['attr_id'], $this->security);
        //     $expression = $this->factValue . $this->fn . $aArgResult;
        //     $evaluatedExpression = $this->expressionLanguageService->evaluateExpression($expression);
        //     return [
        //         'query' => $query,
        //         'expression_result' => $evaluatedExpression
        //     ];
        // }

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
            //  [ENTITY MODEL] : keyword: attribute
            case "string":
                $this->dslArgIsString($arg);
                return['data_type' => 'string'];
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
                return['data_type' => 'integer'];
                break;
            default:
                throw new CustomBadRequestHttpException([
                    'status' => 0,
                    'errorCode' => 'QUERYF100',
                    'errorMessage' => 'Invalid Query Format: Data type of one your arguments has failed, DSL only allows: string, integer, array'
                ], 400);
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

        $this->dslArgExpressionEvaluate($operator, $a, $b);
    }

    public function dslArgExpressionEvaluate($operator, $a, $b)
    {

        if ($a['data_type'] == 'string' && $b['data_type'] == 'string') {
            $attributeFactVal1 = $this->factManager->selectFact($a['attr_id'], $this->security);
            $attributeFactVal2 = $this->factManager->selectFact($b['attr_id'], $this->security);

            if($operator['operator_type'] == 'substract'){
                $expression = $attributeFactVal2 . $operator .  $attributeFactVal1;
            }else{
                $expression = $attributeFactVal1 . $operator . $attributeFactVal2;
            }
           
            $evaluatedExpression = $this->expressionLanguageService->evaluateExpression($expression);
            return $evaluatedExpression;
        }

        if ($a['data_type'] == 'string' && $b['data_type'] == 'integer') {
            $attributeFactVal = $this->factManager->selectFact($a['attr_id'], $this->security);
            $integerArg = $b['value'];

            if($operator['operator_type'] == 'subtract'){
                $expression =  $integerArg . $operator .  $attributeFactVal;
            }else{
                $expression = $attributeFactVal . $operator . $integerArg;
            }

            $evaluatedExpression = $this->expressionLanguageService->evaluateExpression($expression);
            return $evaluatedExpression;
        }

        if ($b['data_type'] == 'string' && $a['data_type'] == 'integer') {
            $attributeFactVal = $this->factManager->selectFact($b['attr_id'], $this->security);
            $integerArg = $a['value'];

            if($operator['operator_type'] == 'subtract'){
                $expression = $attributeFactVal . $operator .  $integerArg;
            }else{
                $expression =  $integerArg . $operator . $attributeFactVal;
            }
            
            $evaluatedExpression = $this->expressionLanguageService->evaluateExpression($expression);
            return $evaluatedExpression;
        }

        //  NB - Ive ruled this out as there is not ruling to suggest that two intergers can be passed as arguments within an expression argument.
        if ($a['data_type']  == 'integer' && $b['data_type'] == 'integer') {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: 2 integer arguments within your expression is not allowed'
            ], 400);
        }
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

                if($fn == '-'){
                    return [
                        'operator' => $fn,
                        'operator_type' => 'subtract'
                    ];
                }
                return $fn;
            }

            if($fn == '-'){
                $this->fnType = 'subtract';
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
}

<?php

namespace App\DomainModels;

use App\Exception\CustomBadRequestHttpException;

class StockpediaBasicModel
{

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
                return 'ADD';
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

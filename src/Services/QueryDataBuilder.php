<?php

namespace App\Services;

use App\DomainModels\JsonDomainModel;
use App\Exception\CustomBadRequestHttpException;


class QueryDataBuilder
{
    /** @var JsonDomainModel $jsonDomainModel */
    private $jsonDomainModel;
    /** @var QueryDataChecker $queryDataChecker */
    private $queryDataChecker;
    /** @var QueryDataExecutor $queryDataExecutor */
    private $queryDataExecutor;

    private $securitySymbolId;
    private $argAExpressionResult;
    private $argBExpressionResult;
    private $evaluatedExpression;

    private array $operators = ["+", "-",  "*", "/"];

    public function __construct(JsonDomainModel $jsonDomainModel, QueryDataChecker $queryDataChecker, QueryDataExecutor $queryDataExecutor)
    {

        $this->jsonDomainModel = $jsonDomainModel;
        $this->queryDataChecker = $queryDataChecker;
        $this->queryDataExecutor = $queryDataExecutor;

        $this->initQueryBuilderProgram();

    }

    public function initQueryBuilderProgram()
    {
        $security = $this->jsonDomainModel->getSecurity();
        $this->securitySymbolId = $this->queryDataExecutor->getSecurityFromDB($security);
        $isDomainModelFnValid = $this->checkOperatorFn();
        $isDomainModelFnSubtract = $this->checkIfFnIsSubtract($this->jsonDomainModel->getFn());
        $getDomainModelExpressionArgTypes = $this->getDataTypes();
        $processModelExpressionArgs = $this->processModelExpressionArgByType($getDomainModelExpressionArgTypes);

        if ($isDomainModelFnSubtract) {
            $expression = $processModelExpressionArgs['b'] . $isDomainModelFnSubtract['operator'] . $processModelExpressionArgs['a'];
            $evaluatedExpression = $this->queryDataExecutor->executeExpression($expression);
        } else {
            $domainModelFn = $this->jsonDomainModel->getFn();
            $expression = $processModelExpressionArgs['a'] . $domainModelFn . $processModelExpressionArgs['b'];
            $evaluatedExpression = $this->queryDataExecutor->executeExpression($expression);
        }

        return $this->evaluatedExpression = $evaluatedExpression;
    }

    public function getEvaluatedResult()
    {
        return $this->evaluatedExpression;
    }

    public function getOperators(): array
    {
        return $this->operators;
    }

    public function checkOperatorFn($isArgAnExpression = false, $argExpressionFn = null)
    {

        $domainModelFn = $this->jsonDomainModel->getFn();

        if (in_array($domainModelFn, $this->getOperators())) {

            if ($isArgAnExpression) {

                if (!$argExpressionFn || !in_array($argExpressionFn, $this->getOperators())) {
                    throw new CustomBadRequestHttpException([
                        'status' => 0,
                        'errorCode' => 'QUERYF100',
                        'errorMessage' => 'Invalid Query Format: You provided an expression argument with an incorrect fn operator'
                    ], 400);
                }

                $isArgExpressionFnSubtract =  $this->checkIfFnIsSubtract($argExpressionFn);

                if (!$isArgExpressionFnSubtract) {
                    return $argExpressionFn;
                }

                return $isArgExpressionFnSubtract;

                //  @TODO Reminder To Check If $domainModelFn == 'subtract'

            }

            return true;
        } else {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: The provided operator does not match our allowed DSL operators'
            ], 400);
        }
    }

    public function getDataTypes($isArgAnExpression = false, $argExpression = null): array
    {
        if ($isArgAnExpression == false && $argExpression == null) {
            $args = [
                'a' => $this->jsonDomainModel->getArgA(),
                'b' => $this->jsonDomainModel->getArgB()
            ];
        }

        if ($isArgAnExpression && $argExpression) {
            $args = $argExpression;
        }

        if (!$isArgAnExpression && $argExpression || !$argExpression && $isArgAnExpression) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'SERVER100',
                'errorMessage' => 'Server Error: Not authorized to run this! ',
            ], 500);
        }


        $argsTypeResults = array();

        foreach ($args as $key => $value) {
            $argsTypeResults[$key] = gettype($value);
        }

        return $argsTypeResults;
    }

    public function checkIfFnIsSubtract(string $fn)
    {
        if ($fn == '-') {
            return [
                'operator' => $fn,
                'operator_type' => 'subtract'
            ];
        } else {
            return false;
        }
    }

    public function processModelExpressionArgByType($arg)
    {
        $argExpressionResults = [
            'a' => null,
            'b' => null
        ];
        foreach ($arg as $key => $value) {

            switch ($value) {
                case "string":
                    $attribute =  $this->processModelExpressionArgByTypeStringeHelper($key);
                    $factValue = $this->queryDataExecutor->getFactValueFromDB($attribute['attr_id'], $this->securitySymbolId);

                    if (array_key_exists($key, $argExpressionResults)) {
                        $argExpressionResults[$key] = $factValue;
                    }
                    break;
                case "integer":
                    $result = $this->processModelExpressionArgByTypeIntegerHelper($key);
                    if (array_key_exists($key, $argExpressionResults)) {
                        $argExpressionResults[$key] = $result;
                    }
                    break;
                case "array":
                    $result = $this->processModelExpressionArgByTypeArrayHelper($key);
                    if (array_key_exists($key, $argExpressionResults)) {
                        $argExpressionResults[$key] = $result;
                    }
            }
        }
        return $argExpressionResults;
    }

    public function processModelExpressionArgByTypeStringeHelper(string $key)
    {
        if ($key == 'a') {
            $a = $this->jsonDomainModel->getArgA();
            $attritbuteName =  $this->queryDataExecutor->getAttributeFromDB($a);
            return $this->argAExpressionResult = $attritbuteName;
        } else {
            $b = $this->jsonDomainModel->getArgB();
            $attritbuteName =  $this->queryDataExecutor->getAttributeFromDB($b);
            return $this->argBExpressionResult = $attritbuteName;
        }
    }

    public function processModelExpressionArgByTypeIntegerHelper(string $key)
    {
        if ($key == 'a') {
            $a = $this->jsonDomainModel->getArgA();
            return $this->argAExpressionResult = $a;
        } else {
            $b = $this->jsonDomainModel->getArgB();
            return $this->argBExpressionResult = $b;
        }
    }

    public function processModelExpressionArgByTypeArrayHelper(string $key)
    {
        if ($key == 'a') {
            $aArgExpression = $this->jsonDomainModel->getArgA();
            $this->checkArgExpressionValueIsValid($aArgExpression);
            $a = $this->evaluateArgExpression($aArgExpression);
            return $this->argAExpressionResult = $a;
        } else {
            $bArgExpression = $this->jsonDomainModel->getArgA();
            $this->checkArgExpressionValueIsValid($bArgExpression);
            $b = $this->evaluateArgExpression($bArgExpression);
            return $this->argAExpressionResult = $b;
        }
    }

    public function checkArgExpressionValueIsValid(array $arg)
    {

        $isArgExpressionValueValid = $this->queryDataChecker->checkIfArray($arg);

        if (!$isArgExpressionValueValid) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: ',
                'passedExpression' => $arg
            ], 400);
        }

        $argCount = $this->queryDataChecker->count($arg, 3);

        if (!$argCount) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: Cannot pass another an expression within an expression argument ',
                'passedExpression' => $arg
            ], 400);
        }

        return true;
    }

    public function evaluateArgExpression(array $arg)
    {
        if (array_key_exists("fn", $arg)) {

            $argExpressionFn = $this->checkOperatorFn(true, $arg['fn']);

            //  Check if operator returned as subtract
            if (isset($argExpressionFn['operator_type'])) {
                $argExpressionFnIsSubtract = $argExpressionFn['operator'];
            }

            $argExpressionValues = [
                'a' => $arg['a'],
                'b' => $arg['b']
            ];

            $getExpressionArgTypes = $this->getDataTypes(true, $argExpressionValues);


            foreach ($getExpressionArgTypes as $key => $value) {
                switch ($value) {

                    case "string":
                        if (array_key_exists($key, $arg)) {
                            $attribute = $this->queryDataExecutor->getAttributeFromDB($arg[$key]);
                            $argExpressionValues[$key] = $this->queryDataExecutor->getFactValueFromDB($attribute['attr_id'], $this->securitySymbolId);
                        } else {
                            throw new CustomBadRequestHttpException([
                                'status' => 0,
                                'errorCode' => 'SERVER100',
                                'errorMessage' => 'Server Error: Could Not Process expression',
                                'passedExpression' => $arg
                            ], 500);
                        }
                        break;
                    case "integer":
                        if (in_array($key, $arg)) {
                            $argExpressionValues[$key] = $arg[$key];
                        }
                        break;
                }
            }

            //  Create Expression To Be Evaluated;
            if ($argExpressionFnIsSubtract) {
                $expression = $argExpressionValues['b'] . $argExpressionFnIsSubtract . $argExpressionValues['a'];
                $evaluatedExpression = $this->queryDataExecutor->executeExpression($expression);
            } else {
                $expression = $argExpressionValues['a'] . $argExpressionFn . $argExpressionValues['b'];
                $evaluatedExpression = $this->queryDataExecutor->executeExpression($expression);
            }

            return $evaluatedExpression;
        } else {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: The missing the fn keyword within your argument expression array',
                'passedExpression' => $arg
            ], 400);
        }
    }
}

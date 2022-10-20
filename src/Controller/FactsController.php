<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;
use App\Traits\StockpediaDomainModelTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Exception\CustomBadRequestHttpException;
use App\Traits\AttributeManagerTrait;
use App\Traits\FactManagerTrait;
use App\Traits\SecurityManagerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class FactsController extends AbstractController
{
    use StockpediaDomainModelTrait;
    use FactManagerTrait;
    use SecurityManagerTrait;
    use AttributeManagerTrait;

    /**
     * @Route("/facts", name="app_facts")
     */
    public function index(): Response
    {
        return $this->render('facts/index.html.twig', [
            'controller_name' => 'FactsController',
        ]);
    }

    /**
     * @Route("/facts/dsl", name="app_facts_dsl")
     * @Method({"POST"})
     *
     * @return Response
     */
    public function dsl(Request $request): JsonResponse
    {

        $searchQuery = json_decode($request->getContent(), true);

        //  Basic Query Ruling

        //  First 3 Levels MUST be set

        //////// FIRST LEVEL - security + expression //////

        // Rule [verb]
        $securitySymbol = $this->domainModel->checkDslQueryFormat($searchQuery, "security");

        if ($securitySymbol) {
            $dbSecuritySymbolId = $this->securityManager->findSecuritySymbol($searchQuery['security']);
        }

        //  Format Check [Noun]
        $expression = $this->domainModel->checkDslQueryFormat($searchQuery, "expression");

        //////// SECOND LEVEL - operator //////

        $expressionOperator = $this->domainModel->checkDslQueryFormat($searchQuery['expression'], "operator");

        // Third Level - fn + arguments

        // Rule [verb]
        $expressionOperatorFn = $this->domainModel->checkDslQueryFormat($searchQuery['expression']['operator'], "fn");

        $expressionOperatorFnExists = $this->domainModel->checkOperatorFn($searchQuery['expression']['operator']["fn"]) ? $searchQuery['expression']['operator']["fn"] : false;


        //  Format Check [Noun]
        $expressionOperationArguments = $this->domainModel->checkDslQueryFormat($searchQuery['expression']['operator'], "arguments");


        ////// FOURTH LEVEL - attribute + number + expression //////

        $expressionOperationArgumentsAttribute = isset($searchQuery['expression']['operator']['arguments']['attribute']) ? $searchQuery['expression']['operator']['arguments']['attribute'] : false;

        if ($expressionOperationArgumentsAttribute) {
            $dbAttributeNameId = $this->attributeManager->findAttributeName($expressionOperationArgumentsAttribute);
        }

        $expressionOperationArgumentsNumber = isset($searchQuery['expression']['operator']['arguments']['number']) ? $searchQuery['expression']['operator']['arguments']['number'] : false;

        if ($expressionOperationArgumentsNumber) {

            if (!is_numeric($expressionOperationArgumentsNumber)) {
                throw new CustomBadRequestHttpException([
                    'status' => 0,
                    'errorCode' => 'QUERYF100',
                    'errorMessage' => 'Invalid Query Format: argument number must be an integer'
                ], 400);
            }
        }

        $expressionOperationArgumentsExpression = isset($searchQuery['expression']['operator']['arguments']['expression']) ? $searchQuery['expression']['operator']['arguments']['expression'] : false;

        $doesExpArgsExist = $this->domainModel->checkDslExpressionArgs($expressionOperationArgumentsAttribute, $expressionOperationArgumentsNumber, $expressionOperationArgumentsExpression);

        // Now Search For Security Fact
        $factQueryValue = $this->factManager->selectFact($dbAttributeNameId, $dbSecuritySymbolId);

        // Finish Off Stockedpedia Search Query
        $resultingOperator = $this->returnOperatorMethod($expressionOperatorFnExists);
        var_dump($resultingOperator);
        return $this->json($factQueryValue);
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

    /**
     * @Route("/facts/create", name="app_facts_create")
     *
     * @return Response
     */
    public function createFact()
    {
        $security = new Security();
        $security->setSymbol('ABC');
        $this->entityManager->persist($security);
        $attribute = new Attribute();
        $attribute->setName('price');
        $this->entityManager->persist($attribute);
        $this->entityManager->flush();

        $fact = new Fact();
        $fact->setValue(4);
        $fact->setAttribute($attribute);
        $fact->setSecurity($security);
        $this->entityManager->persist($fact);
        $this->entityManager->flush();


        return new Response(sprintf('Fact has been created'));
    }

}


//  Notes Cannot have two object keys named expression - so bug/flaw in current DSL
//  Abstract The CustomBadExceptions Out Of Model Layer and Back into Controller
//  Checkn Expression Args does not check for 1 expression sent needs a refactor to check 
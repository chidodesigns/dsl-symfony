<?php

namespace App\Controller;

use App\DomainModels\JsonDomainModel;
use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;
use App\Exception\CustomBadRequestHttpException;
use App\Services\QueryDataBuilder;
use App\Services\QueryDataChecker;
use App\Services\QueryDataExecutor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class FactsController extends AbstractController
{
  
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/facts/dsl", name="app_facts_dsl")
     * @Method({"POST"})
     *
     * @return Response
     */
    public function dsl(Request $request): JsonResponse
    {
        $query = $request->getContent();
        $queryToBeChecked = json_decode($query, true);

        $queryDataChecker = new QueryDataChecker($queryToBeChecked);

        $doesSecurityKeyExist = $queryDataChecker->offsetExists('security');
        $doesExpressionkeyExist = $queryDataChecker->offsetExists('expression');

        if (!$doesSecurityKeyExist || !$doesExpressionkeyExist) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: Your query requires a security and expression'
            ], 400);
        } else {
            $security = $queryDataChecker->offsetGet('security');
            $expression = $queryDataChecker->offsetGet('expression');
        }

        // Process Expression

        //  If array does not have 3 values then invalid
        $isExpressionFormatValid = $queryDataChecker->count($expression, 3);

        if(!$isExpressionFormatValid){
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: Your array expression is formatted incorrectly'
            ], 400);
        }

        $doesFnOperatorExist = $queryDataChecker->searchExpressionArray('fn');
        $doesAExist = $queryDataChecker->searchExpressionArray('a');
        $doesBExist = $queryDataChecker->searchExpressionArray('b');

        if(!$doesFnOperatorExist || !$doesAExist || !$doesBExist  ){
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'QUERYF100',
                'errorMessage' => 'Invalid Query Format: Your expression requires an fn, a, b arguments'
            ], 400);
        }

        //  Create JSON Domain Model 
        try {
            //  Establish Domain Model
            $jsonDomainModel = new JsonDomainModel($security, $expression);
        } catch (CustomBadRequestHttpException $th) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'SERVER100',
                'errorMessage' => 'Server Error: Failed To Create Domain Model'
            ], 500);
        }

        //  Create & Run QueryDataBuilder
        $queryDataExecutor = new QueryDataExecutor($this->entityManager);
        //  Builds & Returns Query
        $queryDataBuilder = new QueryDataBuilder($jsonDomainModel, $queryDataChecker, $queryDataExecutor);


   
        return $this->json([
            'query' => $queryToBeChecked,
            'query_result' => $queryDataBuilder->getEvaluatedResult()
        ]);
    }

    /**
     * @Route("/facts/create", name="app_facts_create")
     *
     * @return Response
     */
    public function createFact()
    {
        $symbols = array('ABC', 'BCD', 'CDE', 'DEF', 'EFG', 'FGH', 'GHI', 'HIJ', 'IJK', 'JKL');
        $attrs = array('price', 'eps', 'dps', 'sales', 'ebitda', 'free_cash_flow', 'assets', 'liabilities', 'debt', 'shares');

        foreach ($symbols as $value) {
            $security = new Security();
            $security->setSymbol($value);
            $this->entityManager->persist($security);
            $this->entityManager->flush();
        }

        foreach ($attrs as $value) {
            $attribute = new Attribute();
            $attribute->setName($value);
            $this->entityManager->persist($attribute);
            $this->entityManager->flush();
        }

        $securityRepo = $this->entityManager->getRepository(Security::class);
        $securities = $securityRepo->findAll();
        $attributeRepo = $this->entityManager->getRepository(Attribute::class);
        $attributes = $attributeRepo->findAll();


        foreach ($securities as $security) {
            foreach ($attributes as $attribute) {
                $fact = new Fact();
                $fact->setValue(rand(0, 50));
                $fact->setAttribute($attribute);
                $fact->setSecurity($security);
                $this->entityManager->persist($fact);
                $this->entityManager->flush();
            }
        }


        return new Response(sprintf('Fact has been created'));
    }
}


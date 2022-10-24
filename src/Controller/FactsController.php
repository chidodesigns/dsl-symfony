<?php

namespace App\Controller;

use App\DomainModels\JsonDomainModel;
use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;
use App\Exception\CustomBadRequestHttpException;
use App\Services\QueryDataChecker;
use App\Traits\EntityManagerTrait;
use App\Traits\StockpediaDomainModelTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class FactsController extends AbstractController
{
    use EntityManagerTrait;
    use StockpediaDomainModelTrait;


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

        var_dump($jsonDomainModel);

        // $queryBuildResponse = $this->domainModel->dslQueryBuilder($searchQuery);

        return $this->json($queryDataChecker);
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


//  Notes Cannot have two object keys named expression - so bug/flaw in current DSL
//  Abstract The CustomBadExceptions Out Of Model Layer and Back into Controller 
//  JSON Decode vs Symfony Serializer (had issues trying to get this installed)
//  Using POSTMAN had issues duplicating two fields in JSON post BODY so have not built/test for two arguments of the same type.
//  After building v1 I reaslise the current UML model of the DSL is to verbose - which I created - therefore the querybuilder is very rudimentary and only tackles a few use cases - it needs to be more dynamic.
//  Better use for handling expressions would be using the Symfony Expression Language it would allow expressions and expression methods to be created a lot more smoothly.

//  Postman Req:

// {
//     "security": "ABC",
//     "expression": {
//         "operator": {
//             "fn": "+",
//             "arguments": {
//                 "attribute": "price",
//                 "number": 2
//             }
//         }
//     }
// }
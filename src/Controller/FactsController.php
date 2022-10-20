<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;
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

        $queryBuildResponse = $this->domainModel->dslQueryBuilder($searchQuery);

        return $this->json($queryBuildResponse);
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

        foreach($symbols as $value){
            $security = new Security();
            $security->setSymbol($value);
            $this->entityManager->persist($security);
            $this->entityManager->flush();
        }

        foreach($attrs as $value){
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
            foreach($attributes as $attribute){
                $fact = new Fact();
                $fact->setValue(rand(0,50));
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
//  Checkn Expression Args does not check for 1 expression sent needs a refactor to check 
//  JSON Decode vs Symfony Serializer (had issues trying to get this installed)
//  Using POSTMAN had issues duplicating two fields in JSON post BODY so have not built/test for two arguments of the same type.
//  After building v1 I reaslise the current UML model of the DSL is to verbose - which I created - therefore the querybuilder is very rudimentary and only tackles a few use cases - it needs to be more dynamic.
//  Better use for handling expressions would be using the Symfony Expression Language it would allow expressions and expression methods to be created a lot more smoothly.
//  Would need to add a fixtures function loop through both data sets in security and attribute and create both entities with a value assigned next to them for the fact's table.

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
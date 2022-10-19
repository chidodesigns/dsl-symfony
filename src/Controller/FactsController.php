<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;
use App\Traits\FactRepositoryTrait;
use App\Traits\EntityManagerTrait;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

class FactsController extends AbstractController
{
    use EntityManagerTrait;
    use FactRepositoryTrait;

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
    public function dsl(Request $request):JsonResponse
    {
         $searchQuery = json_decode($request->getContent(), true);

        //  Basic Query Ruling

        //  First 3 Levels MUST be set
        
         //////// FIRST LEVEL - security + expression //////

         // Rule [verb]
         $securitySymbol = $this->checkDslQueryFormat($searchQuery, "security");

        if($securitySymbol){
            $dbSecuritySymbolId = $this->findSecuritySymbol($searchQuery['security']);
        }

        //  Format Check [Noun]
         $expression = $this->checkDslQueryFormat($searchQuery, "expression");

         //////// SECOND LEVEL - operator //////

         $expressionOperator = $this->checkDslQueryFormat($searchQuery['expression'], "operator");

         // Third Level - fn + arguments

         // Rule [verb]
         $expressionOperatorFn = $this->checkDslQueryFormat($searchQuery['expression']['operator'], "fn");

        if($expressionOperatorFn){
            $expressionOperatorFnExists = $this->checkOperatorFn($searchQuery['expression']['operator']["fn"]) ? $searchQuery['expression']['operator']["fn"] : false ;
        }

        //  Format Check [Noun]
         $expressionOperationArguments = $this->checkDslQueryFormat($searchQuery['expression']['operator'], "arguments");


         ////// FOURTH LEVEL - attribute + number + expression //////

         $expressionOperationArgumentsAttribute = isset($searchQuery['expression']['operator']['arguments']['attribute']) ? $searchQuery['expression']['operator']['arguments']['attribute'] : false;

         if($expressionOperationArgumentsAttribute){
             $dbAttributeNameId = $this->findAttributeName($expressionOperationArgumentsAttribute);
         }

         $expressionOperationArgumentsNumber = isset($searchQuery['expression']['operator']['arguments']['number']) ? $searchQuery['expression']['operator']['arguments']['number'] : false;

         $expressionOperationArgumentsExpression = isset($searchQuery['expression']['operator']['arguments']['expression']) ? $searchQuery['expression']['operator']['arguments']['expression'] : false;

         $doesExpArgsExist = $this->checkDslExpressionArgs($expressionOperationArgumentsAttribute,$expressionOperationArgumentsNumber, $expressionOperationArgumentsExpression);

         // Now Search For Security Fact
         
       
         return $this->json('hello');
      
    }

    public function checkDslQueryFormat($dslArray, string $dslArrayTtitle)
    {
        if(!isset($dslArray)){
             //  @TODO put this in a proper event listener for a bad request
            throw new Exception('Wrong Format');
        }
        return array_key_exists($dslArrayTtitle, $dslArray);
    }

    public function checkDslExpressionArgs($attribute,$number,$expression){
        if(!$attribute && !$number && !$expression)  {
            //  @TODO put this in a proper event listener for a bad request
            throw new Exception('Your Operator Must have arguments');
        }elseif($attribute && $number && $expression){
           //  @TODO put this in a proper event listener for a bad request
           throw new Exception('Wrong query format, you MUST have only 2 arguments');
        }else{
            //  @TODO function to loop through array and return back array values
            return true;
        }
    }

    // Security
    public function findSecuritySymbol(string $symbol)
    {
        $repo = $this->getDoctrine()->getRepository(Security::class);
        $secSymbol = $repo->findOneBy(['symbol' => $symbol]);
        if(!$secSymbol){
            throw new Exception('No Security Found With This Symbol');
        }
        return $secSymbol->getId();
    }

     // Attribute
     public function findAttributeName(string $name)
     {
         $repo = $this->getDoctrine()->getRepository(Attribute::class);
         $attrName = $repo->findOneBy(['name' => $name]);

         if(!$attrName){
             throw new Exception('No Attribute Found With This Name');
         }

         return $attrName->getId();
     }

    // Check Fn Operator
    public function checkOperatorFn(string $fn)
    {
        $operators = ["+", "-",  "*", "/"];
        if(in_array($fn, $operators)){
            return true;
        }else{
            throw new Exception('Operator fn not found');
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

    /**
     * @Route("/facts/find", name="app_facts_find")
     *
     * @return Response
     */
    public function selectFact()
    {
        $attribute = $this->entityManager->find(Attribute::class,1);
        $security = $this->entityManager->find(Security::class,1);
      $result = $this->factRepository->getFact($attribute,$security);
       dd($result);
    }
}


//  Notes Cannot have two object keys named expression - so bug/flaw in current DSL
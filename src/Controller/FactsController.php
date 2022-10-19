<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;
use App\Repository\FactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

class FactsController extends AbstractController
{
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
         $result = json_decode($request->getContent(), true);
         $securitySymbol = $result['security'];
         $expression = $result['expression'];
         return $this->json($securitySymbol);
      
    }

    /**
     * @Route("/facts/create", name="app_facts_create")
     *
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function createFact(EntityManagerInterface $entityManager)
    {
        $security = new Security();
        $security->setSymbol('ABC');
        $entityManager->persist($security);
        $attribute = new Attribute();
        $attribute->setName('price');
        $entityManager->persist($attribute);
        $entityManager->flush();

        $fact = new Fact();
        $fact->setValue(4);
        $fact->setAttribute($attribute);
        $fact->setSecurity($security);
        $entityManager->persist($fact);
        $entityManager->flush();
        // $fact->addFacts($attribute,$security, 4);

        return new Response(sprintf('Fact has been created'));
    }

    /**
     * @Route("/facts/find", name="app_facts_find")
     *
     * @param FactRepository $factRepository
     * @return Response
     */
    public function selectFact(FactRepository $factRepository, EntityManagerInterface $entityManager)
    {
        $attribute = $entityManager->find(Attribute::class,1);
        $security = $entityManager->find(Security::class,1);
      $result = $factRepository->getFact($attribute,$security);
       dd($result);
    }
}

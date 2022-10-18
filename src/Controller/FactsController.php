<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/facts/create", name="app_facts_create")
     *
     * @param EntityManagerInterface $entityManager
     * @return void
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
}

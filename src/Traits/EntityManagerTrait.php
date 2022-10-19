<?php
namespace App\Traits;

use Doctrine\ORM\EntityManagerInterface;

trait EntityManagerTrait 
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @Required
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
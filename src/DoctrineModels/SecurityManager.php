<?php
namespace App\DoctrineModels;

use App\Entity\Security;
use App\Exception\CustomBadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;

class SecurityManager 
{

    public function findSecuritySymbol(EntityManagerInterface $entityManager,string $symbol)
    {
        $repo = $entityManager->getRepository(Security::class);
        $secSymbol = $repo->findOneBy(['symbol' => $symbol]);
        if (!$secSymbol) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'ENSEC100',
                'errorMessage' => 'Entity Not Found'
            ], 404);
        }
        return $secSymbol->getId();
    }

}
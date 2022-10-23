<?php
namespace App\DoctrineModels;

use App\Traits\EntityManagerTrait;
use App\Entity\Security;
use App\Exception\CustomBadRequestHttpException;

class SecurityManager 
{
    use EntityManagerTrait;

    public function findSecuritySymbol(string $symbol)
    {
        $repo = $this->entityManager->getRepository(Security::class);
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
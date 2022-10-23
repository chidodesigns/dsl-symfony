<?php 
namespace App\DoctrineModels;

use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;
use App\Traits\FactRepositoryTrait;
use App\Exception\CustomBadRequestHttpException;
use App\Traits\EntityManagerTrait;

class FactManager
{
    use FactRepositoryTrait;
    use EntityManagerTrait;

    public function selectFact(int $attributeId, int $secSymbolId)
    {
        $attribute = $this->entityManager->find(Attribute::class, $attributeId);
        $security = $this->entityManager->find(Security::class, $secSymbolId);
        $result = $this->factRepository->getFact($attribute, $security);
        if (!$result) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'ENTITY100',
                'errorMessage' => 'Entity Not Found'
            ], 404);
        }
        return $result->getValue();
    }


}
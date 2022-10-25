<?php 
namespace App\DoctrineModels;

use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;

use App\Exception\CustomBadRequestHttpException;

use Doctrine\ORM\EntityManagerInterface;

class FactManager
{
 
    public function selectFact(EntityManagerInterface $entityManager,int $attributeId, int $secSymbolId)
    {
        $attribute = $entityManager->find(Attribute::class, $attributeId);
        $security = $entityManager->find(Security::class, $secSymbolId);
        $repo = $entityManager->getRepository(Fact::class);
        $result = $repo->getFact($attribute, $security);
        if (!$result) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'ENFACT100',
                'errorMessage' => 'Entity Not Found'
            ], 404);
        }
        return $result->getValue();
    }


}
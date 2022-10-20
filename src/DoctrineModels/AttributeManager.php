<?php
namespace App\DoctrineModels;

use App\Traits\EntityManagerTrait;
use App\Entity\Attribute;
use App\Exception\CustomBadRequestHttpException;

class AttributeManager 
{
    use EntityManagerTrait;

    public function findAttributeName(string $name)
    {
        $repo = $this->entityManager->getRepository(Attribute::class);
        $attrName = $repo->findOneBy(['name' => $name]);

        if (!$attrName) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'ENTITY100',
                'errorMessage' => 'Entity Not Found'
            ], 404);
        }
        return $attrName->getId();
    }

}
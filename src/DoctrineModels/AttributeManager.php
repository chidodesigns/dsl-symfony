<?php
namespace App\DoctrineModels;

use App\Entity\Attribute;
use App\Exception\CustomBadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;

class AttributeManager 
{
 

    public function findAttributeName(EntityManagerInterface $entityManager, string $name)
    {
        $repo = $entityManager->getRepository(Attribute::class);
        $attrName = $repo->findOneBy(['name' => $name]);

        if (!$attrName) {
            throw new CustomBadRequestHttpException([
                'status' => 0,
                'errorCode' => 'ENATTR100',
                'errorMessage' => 'Entity Not Found'
            ], 404);
        }
        return [
            'attr_id' => $attrName->getId(),
            'attr_name' => $attrName->getName()
        ];
    }

}
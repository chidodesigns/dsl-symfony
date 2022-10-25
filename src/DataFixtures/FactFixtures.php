<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FactFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $attrs = array('price', 'eps', 'dps', 'sales', 'ebitda', 'free_cash_flow', 'assets', 'liabilities', 'debt', 'shares');

        $symbols = array('ABC', 'BCD', 'CDE', 'DEF', 'EFG', 'FGH', 'GHI', 'HIJ', 'IJK', 'JKL');

        foreach ($symbols as $value) {
            $security = new Security();
            $security->setSymbol($value);
            $manager->persist($security);
            $manager->flush();
        }

        foreach ($attrs as $value) {
            $attribute = new Attribute();
            $attribute->setName($value);
            $manager->persist($attribute);
            $manager->flush();
        }

        $securityRepo = $manager->getRepository(Security::class);
        $securities = $securityRepo->findAll();
        $attributeRepo = $manager->getRepository(Attribute::class);
        $attributes = $attributeRepo->findAll();


        foreach ($securities as $security) {
            foreach ($attributes as $attribute) {
                $fact = new Fact();
                $fact->setValue($security->getId() * $attribute->getId());
                $fact->setAttribute($attribute);
                $fact->setSecurity($security);
                $manager->persist($fact);
                $manager->flush();
            }
        }
    }
}

<?php

namespace App\Tests\Unit\DoctrineModels;

use App\DoctrineModels\FactManager;
use App\DoctrineModels\SecurityManager;
use App\DoctrineModels\AttributeManager;
use App\Exception\CustomBadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use TypeError;

class FactManagerTest extends KernelTestCase
{
    /**
     * 
     *@var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SecurityManager $securityManager 
     */
    protected $securityManager;

    /**
     * @var AttributeManager $attributeManager 
     */
    protected $attributeManager;

    /**
     * @var FactManager $factManager 
     */
    protected $factManager;

    protected $securitySymbol;
    protected $attributeName;

    public function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;

        $this->entityManager = $container->get(EntityManagerInterface::class);

        $this->securityManager = new SecurityManager;
        $this->securitySymbol = $this->securityManager->findSecuritySymbol($this->entityManager, 'ABC');

        $this->attributeManager = new AttributeManager;
        $this->attributeName = $this->attributeManager->findAttributeName($this->entityManager, 'price');

        $this->factManager = new FactManager;
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function the_select_fact_fn_returns_a_fact_value()
    {
        $factValue = $this->factManager->selectFact($this->entityManager, $this->attributeName['attr_id'], $this->securitySymbol);

        $this->assertEquals(1, $factValue);
    }

    /** @test */
    public function the_select_fact_fn_returns_typeErrorException_when_integers_are_passed_that_do_not_match_id_values_of_attributes_and_securities()
    {
        $this->expectException(TypeError::class);
        $factValue = $this->factManager->selectFact($this->entityManager, 20, 22);
        $this->assertEmpty($factValue);
    }
}

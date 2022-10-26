<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Fact;
use App\Entity\Security;
use App\Entity\Attribute;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FactTest extends KernelTestCase
{

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /** @var Security $security */
    protected $security;

    /** @var Attribute $attribute */
    protected $attribute;

    /** @var Fact $attribute */
    protected $fact;

    public function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->fact = $this->entityManager->getRepository(Fact::class)->findOneBy(['value' => '1']);
        $this->security = $this->entityManager->getRepository(Security::class)->findOneBy(['symbol' => 'ABC']);
        $this->attribute = $this->entityManager->getRepository(Attribute::class)->findOneBy(['name' => 'price']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function the_get_value_fn_works()
    {
        $this->assertEquals(1, $this->fact->getValue());
    }

    /** @test */
    public function the_set_value_fn_works()
    {
        $this->fact->setValue(2);

        $this->assertEquals(2, $this->fact->getValue());
    }

    /** @test */
    public function the_set_attribute_fn_works()
    {
        $this->fact->setAttribute($this->attribute);

        $this->assertIsObject($this->fact->getAttribute());
    }

     /** @test */
     public function the_get_attribute_fn_works()
     {
         $this->assertIsObject($this->fact->getAttribute());
     }

     /** @test */
    public function the_set_security_fn_works()
    {
        $this->fact->setSecurity($this->security);

        $this->assertIsObject($this->fact->getSecurity());
    }


     /** @test */
     public function the_get_security_fn_works()
     {
         $this->assertIsObject($this->fact->getSecurity());
     }
}

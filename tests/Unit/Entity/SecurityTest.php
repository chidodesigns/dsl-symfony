<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class  SecurityTest extends KernelTestCase
{

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /** @var Security $attribute */
    protected $security;

    public function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->security = $this->entityManager->getRepository(Security::class)->findOneBy(['symbol' => 'ABC']);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function the_get_id_fn_works()
    {
        $this->assertEquals('1', $this->security->getId());
    }

    // /** @test */
    public function the_set_symbol_fn_works()
    {
        $this->security->setSymbol('BCD');

        $this->assertEquals('BCD', $this->security->getSymbol());
    }


    // /** @test */
    public function the_get_symbol_fn_works()
    {

        $this->assertEquals('ABC', $this->security->getSymbol());
    }
}

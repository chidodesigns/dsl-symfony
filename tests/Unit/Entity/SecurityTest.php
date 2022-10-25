<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Security;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
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
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        
        $this->security = new Security();
        $this->security->setSymbol('ABC');
        $this->entityManager->persist($this->security);
        $this->entityManager->flush();
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

    /** @test */
    public function the_set_symbol_fn_works()
    {
        $this->security->setSymbol('BCD');

        $this->assertEquals('BCD', $this->security->getSymbol());
    }


    /** @test */
    public function the_get_symbol_fn_works()
    {

        $this->assertEquals('ABC', $this->security->getSymbol());
    }
}

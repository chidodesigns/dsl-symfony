<?php

namespace App\Tests\Unit\DoctrineModels;

use App\DoctrineModels\SecurityManager;
use App\Exception\CustomBadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SecurityManagerTest extends KernelTestCase
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

    public function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;

        $this->entityManager = $container->get(EntityManagerInterface::class);

        $this->securityManager = new SecurityManager;
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function the_find_security_name_fn_returns_an_id()
    {
        $securitySymbol = $this->securityManager->findSecuritySymbol($this->entityManager, 'ABC');
        $this->assertIsInt($securitySymbol);
    }

    // /** @test */
    public function the_find_security_symbol_fn_returns_correct_id()
    {
        $securitySymbol = $this->securityManager->findSecuritySymbol($this->entityManager, 'ABC');
        $this->assertEquals(1, $securitySymbol);
    }

    // /** @test */
    public function the_find_security_symbol_fn_returns_a_customBadRequestHttpException_when_no_symbol_is_found()
    {
        $this->expectException(CustomBadRequestHttpException::class);
        $securitySymbol = $this->securityManager->findSecuritySymbol($this->entityManager, 'ZYZ');
        $this->assertEmpty($securitySymbol);
    }
}

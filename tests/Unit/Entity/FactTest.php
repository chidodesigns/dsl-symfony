<?php 
namespace App\Tests\Unit\Entity;

use App\Entity\Fact;
use App\Entity\Security;
use App\Entity\Attribute;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class  FactTest extends KernelTestCase
{

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /** @var Fact $attribute */
    protected $fact;

    public function setUp():void
    {
        $kernel = self::bootKernel();
      
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        $attribute = new Attribute();
        $attribute->setName('eps');
        $this->entityManager->persist($attribute);
        $this->entityManager->flush();

        $security = new Security();
        $security->setSymbol('BDE');
        $this->entityManager->persist($security);
        $this->entityManager->flush();
        
        $this->fact = new Fact();
        $this->fact->setValue(10);
        $this->fact->setAttribute($attribute);
        $this->fact->setSecurity($security);
        $this->entityManager->persist($this->fact);
        $this->entityManager->flush();

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function the_get_security_fn_works()
    {
        $this->assertEquals('BDE', $this->fact->getSecurity());
    }


}
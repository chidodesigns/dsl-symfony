<?php 
namespace App\Tests\Unit\Entity;

use App\Entity\Attribute;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AttributeTest extends KernelTestCase
{

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /** @var Attribute $attribute */
    protected $attribute;

    public function setUp():void
    {
        $kernel = self::bootKernel();
     
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        
        $this->attribute = new Attribute();
        $this->attribute->setName('sales');
        $this->entityManager->persist($this->attribute);
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
        $this->assertEquals('1', $this->attribute->getId());
    }

    /** @test */
    public function the_set_name_fn_works()
    {
        $this->attribute->setName('eps');

        $this->assertEquals('eps', $this->attribute->getName());
        
    }


    /** @test */
    public function the_get_name_fn_works()
    {
    
        $this->assertEquals('sales', $this->attribute->getName());
        
    }

}
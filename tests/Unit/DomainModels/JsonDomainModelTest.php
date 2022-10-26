<?php

namespace App\Tests\Unit\DomainModels;

use App\DomainModels\JsonDomainModel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class JsonDomainModelTest extends KernelTestCase
{
    /** @var JsonDomainModel $jsonDomainModel */
    protected $jsonDomainModel;

    protected string $security;
    protected array $expression;

    public function setUp(): void
    {

        $this->security = 'ABC';

        $this->expression = [
            "fn" => "+",
            "a" => "price",
            "b" => 20
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /** @test */
    public function json_domain_model_class_can_be_created_and_returns_and_obj()
    {
        $this->jsonDomainModel = new JsonDomainModel($this->security, $this->expression);
        $this->assertIsObject($this->jsonDomainModel);
    }

    /** @test */
    public function json_domain_model_class_returns_security_as_string()
    {
        $this->jsonDomainModel = new JsonDomainModel($this->security, $this->expression);
        $this->assertIsString($this->jsonDomainModel->getSecurity());
        $this->assertEquals('ABC', $this->jsonDomainModel->getSecurity());
    }

    /** @test */
    public function json_domain_model_class_returns_expression_as_array()
    {
        $this->jsonDomainModel = new JsonDomainModel($this->security, $this->expression);
        $this->assertIsArray($this->jsonDomainModel->getExpression());
        $this->assertEquals([
            "fn" => "+",
            "a" => "price",
            "b" => 20
        ], $this->jsonDomainModel->getExpression());
    }

    /** @test */
    public function json_domain_model_get_fn_works()
    {
        $this->jsonDomainModel = new JsonDomainModel($this->security, $this->expression);
        $this->assertEquals('+', $this->jsonDomainModel->getFn());
    }

    /** @test */
    public function json_domain_model_get_arg_a_works()
    {
        $this->jsonDomainModel = new JsonDomainModel($this->security, $this->expression);
        $this->assertEquals("price", $this->jsonDomainModel->getArgA());
    }

    /** @test */
    public function json_domain_model_get_arg_b_works()
    {
        $this->jsonDomainModel = new JsonDomainModel($this->security, $this->expression);
        $this->assertEquals(20, $this->jsonDomainModel->getArgB());
    }
}

<?php

namespace App\Tests\Unit\Services;

use App\Services\ExpressionLanguageService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class ExpressionLanguageServiceTest extends KernelTestCase
{

     /** @var ExpressionLanguageService $expressionLanguageService */
     protected $expressionLanguageService;

    protected $expression;

    public function setUp(): void
    {
        $this->expressionLanguageService = new ExpressionLanguageService();

        $this->expression = "1 + 2";
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /** @test */
    public function expression_language_service_evaluate_expression_fn_returns_a_string_evaluation()
    {
        $evaluatedExpression = $this->expressionLanguageService->evaluateExpression($this->expression);
        $this->assertIsString($evaluatedExpression);
        $this->assertEquals("3", $evaluatedExpression);
    }
}

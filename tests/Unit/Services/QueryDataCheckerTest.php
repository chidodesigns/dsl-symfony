<?php

namespace App\Tests\Unit\Services;

use App\Services\QueryDataChecker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class QueryDataCheckerTest extends KernelTestCase
{
    /** @var QueryDataChecker $queryDataChecker */
    protected $queryDataChecker;

    protected array $expression;

    public function setUp(): void
    {

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
    public function query_data_checker_class_can_be_created_and_returns_and_obj()
    {
        $this->queryDataChecker = new QueryDataChecker($this->expression);
        $this->assertIsObject($this->queryDataChecker);
    }

    /** @test */
    public function query_data_checker_get_array_expression_fn_reutrns_array()
    {
        $this->queryDataChecker = new QueryDataChecker($this->expression);
        $this->assertIsArray($this->queryDataChecker->getArrayExpression());
    }

    /** @test */
    public function query_data_checker_offset_exists_fn_returns_boolean()
    {
        $this->queryDataChecker = new QueryDataChecker($this->expression);
        $this->assertIsBool($this->queryDataChecker->offsetExists('fn'));
        $this->assertEquals(true, $this->queryDataChecker->offsetExists('fn'));
    }

    /** @test */
    public function query_data_checker_offset_get_fn_returns_offset_value()
    {
        $this->queryDataChecker = new QueryDataChecker($this->expression);
        $this->assertEquals('price', $this->queryDataChecker->offsetGet('a'));
        $this->assertIsString($this->queryDataChecker->offsetGet('a'));
    }

    /** @test */
    public function query_data_checker_offset_set_fn_sets_an_new_on_given_offset()
    {
        $this->queryDataChecker = new QueryDataChecker($this->expression);
        $this->queryDataChecker->offsetSet('b', 'eps');
        $newOffsetValue = $this->queryDataChecker->offsetGet('b');
        $this->assertEquals('eps', $newOffsetValue);
        $this->assertIsString($this->queryDataChecker->offsetGet('b'));
    }

    /** @test */
    public function query_data_checker_returns_bool_after_array_count()
    {
        $expression = [
            "fn" => "+",
            "a" =>  [
                "fn" => "-",
                "a" =>  "price",
                "b" => 20
            ],
            "b" => "sales"
        ];
        $this->queryDataChecker = new QueryDataChecker($expression);
        $a = $this->queryDataChecker->offsetGet('a');
        $this->assertIsBool($this->queryDataChecker->count($a, 3));
        $this->assertEquals(true, $this->queryDataChecker->count($a, 3));
    }

    /** @test */
    public function query_data_checker_check_if_array_returns_a_bool()
    {
        $this->queryDataChecker = new QueryDataChecker($this->expression);
        $this->assertIsBool($this->queryDataChecker->checkIfArray($this->expression));
        $this->assertEquals(true, $this->queryDataChecker->checkIfArray($this->expression));
    }

    /** @test */
    public function query_data_checker_search_expression_array_returns_bool()
    {
        $expression = [
            "security" =>  "ABC",
            "expression" =>  [
                "fn" =>  "+",
                "a" =>  [
                    "fn" => "-",
                    "a" =>  "price",
                    "b" =>  20
                ],
                "b" => "sales"
            ]
        ];
        $this->queryDataChecker = new QueryDataChecker($expression);
        $this->assertIsBool($this->queryDataChecker->searchExpressionArray('fn'));
        $this->assertEquals(true, $this->queryDataChecker->searchExpressionArray('fn'));
    }
}

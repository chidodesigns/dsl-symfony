<?php

namespace App\Tests\Unit\Services;

use App\DomainModels\JsonDomainModel;
use App\Services\QueryDataChecker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class QueryDataBuilderTest extends KernelTestCase
{

    /** @var JsonDomainModel $jsonDomainModel */
    protected $jsonDomainModel;

    /** @var QueryDataChecker $queryDataChecker */
    protected $queryDataChecker;

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

        $this->jsonDomainModel = new JsonDomainModel($this->security, $this->expression);
        $this->queryDataChecker = new QueryDataChecker($this->expression);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

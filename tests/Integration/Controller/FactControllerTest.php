<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FactControllerTest extends WebTestCase
{
    public $client;

    protected function setUp(): void
    {
        $this->client = static::createClient(array(), array(), [
            "HTTP_CONTENT_TYPE" => 'application/json',
            "CONTENT_TYPE" => 'application/json',
            " content-type" => 'application/json'
        ]);
    }

    /** @test */
    public function dslRoute()
    {
        $expression = array(

            'security' => 'ABC',

            'expression' => [
                "fn" => "+",
                "a" => [
                    "fn" => "-",
                    "a" => "price",
                    "b" => 20
                ],
                "b" => 'sales'
            ]

        );
        $this->client = static::createClient();

        $crawler = $this->client->request('POST', '/facts/dsl', [], [], [
            "HTTP_CONTENT_TYPE" => 'application/json',
        ],json_encode($expression));

        $this->assertResponseIsSuccessful();
    }
}

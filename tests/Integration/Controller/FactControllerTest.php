<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FactControllerTest extends WebTestCase
{

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
        $client = static::createClient([], [
            'HTTP_CONTENT_TYPE'       => 'application/json',
        ]);
        $client->request('POST', '/facts/dsl', $expression);

        $request = $client->getRequest();

        $this->assertResponseIsSuccessful();
    }
}

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

        $response = $this->client->request('POST', '/facts/dsl', [], [], [
            "HTTP_CONTENT_TYPE" => 'application/json',
        ],json_encode($expression));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = $this->client->getResponse()->getContent();

        // var_dump(json_decode($responseContent));
    }

    /** @test */
    public function getResponseContentFromDsl()
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

        $response = $this->client->request('POST', '/facts/dsl', [], [], [
            "HTTP_CONTENT_TYPE" => 'application/json',
        ],json_encode($expression));

        $this->assertResponseIsSuccessful();
        $responseContent = $this->client->getResponse()->getContent();
        $responseContentDecoded = json_decode($responseContent);

        $this->assertIsObject($responseContentDecoded->query); 
        $this->assertIsString($responseContentDecoded->query->security);    
        $this->assertIsObject($responseContentDecoded->query->expression);    
        $this->assertIsString($responseContentDecoded->query->expression->fn);   
        $this->assertIsObject($responseContentDecoded->query->expression->a);  
        $this->assertIsString($responseContentDecoded->query->expression->b);    

    }

      /** @test */
      public function getResponseContentValuesFromDsl()
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
  
          $response = $this->client->request('POST', '/facts/dsl', [], [], [
              "HTTP_CONTENT_TYPE" => 'application/json',
          ],json_encode($expression));
  
          $this->assertResponseIsSuccessful();
          $responseContent = $this->client->getResponse()->getContent();
          $responseContentDecoded = json_decode($responseContent);
  
          $this->assertEquals('ABC', $responseContentDecoded->query->security);    
          $this->assertEquals('23', $responseContentDecoded->query_result);     
  
      }
}

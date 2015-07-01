<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\Entity;
use Acquia\ContentHubClient\ContentHub;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class ContentHubTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Acquia\ContentHubClient\ContentHub
     */
    private function getClient()
    {
        return new ContentHub('public', 'secret', 'origin');
    }

    private function setData()
    {
        return [
            'data' => [
                'uuid' => '00000000-0000-0000-0000-000000000000',
                'origin' => '11111111-0000-0000-0000-000000000000',
                'data' => [
                    'uuid' => '00000000-0000-0000-0000-000000000000',
                    "type" => "product",
                    "created" => "2014-12-21T20:12:11+00:00Z",
                    "modified" => "2014-12-21T20:12:11+00:00Z",
                    "attributes" => [
                        "title" => [
                            "type" => "string",
                            "value" => [
                                "en" => "A",
                                "hu" => "B",
                                "und" => "C",
                            ],
                        ],
                    ],
                    "assets" => [
                        [
                            "url" => "http://acquia.com/sites/default/files/foo.png",
                            "replace-token" => "[acquia-logo]",
                        ],
                        [
                            "url" => "http://acquia.com/sites/default/files/bar.png",
                            "replace-token" => "[acquia-thumb]",
                        ],
                    ],
                ],
            ],
        ];
    }

    private function setDefinition()
    {
        return [
          'children' => [
              0 => '/settings',
              1 => '/register',
              2 => '/entities',
              3 => '/ping',
              4 => '/elastic',
          ],
        ];
    }

    public function testPing()
    {
        $data = [
            'success' => 1,
        ];
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Pinging the service
        $response = $client->ping();
        $this->assertEquals($data, $response->json());
    }

    public function testDefinition()
    {
        $data = $this->setDefinition();

        $client = $this->getClient();

        $mock = new Mock();
        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        $response = $client->definition();
        $this->assertEquals($data, $response);
    }

    public function testClientByName()
    {
        $data = [
            'name' => 'mysite',
            'uuid' => '00000000-0000-0000-0000-000000000000',
        ];

        $client = $this->getClient();

        $mock = new Mock();
        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        $response = $client->getClientByName('mysite');
        $this->assertEquals($data, $response);
    }

    public function testCreateEntity()
    {
        $data = [
            'success' => true,
        ];
        $resource = 'http://acquia.com/content_hub_connector/node/00000000-0000-0000-0000-000000000000';
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Create an Entity
        $response = $client->createEntity($resource);
        $this->assertEquals($mockResponse->json(), $response->json());
        $this->assertEquals($mockResponse, $response);

        // Create one or more entities
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);
        $response = $client->createEntities($resource);
        $this->assertEquals($mockResponse->json(), $response->json());
        $this->assertEquals($mockResponse, $response);

    }

    public function testReadEntity()
    {
        $data = $this->setData();
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Read an Entity
        $entity = $client->readEntity('00000000-0000-0000-0000-000000000000');
        $this->assertEquals(6, count($entity));
        $this->assertEquals($data['data']['data'], (array) $entity);
    }

    public function testUpdateEntity()
    {
        $data = [
            'success' => true,
        ];
        $resource = 'http://acquia.com/content_hub_connector/node/00000000-0000-0000-0000-000000000000';
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Update an Entity
        $response = $client->updateEntity($resource, '00000000-0000-0000-0000-000000000000');
        $this->assertEquals($mockResponse->json(), $response->json());
        $this->assertEquals($mockResponse, $response);

        // Test Update Entities (one or more)
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);
        $response = $client->updateEntities($resource);
        $this->assertEquals($mockResponse->json(), $response->json());
        $this->assertEquals($mockResponse, $response);
    }

    public function testDeleteEntity()
    {
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponse = new Response(200);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Delete an Entity
        $response = $client->deleteEntity('00000000-0000-0000-0000-000000000000');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
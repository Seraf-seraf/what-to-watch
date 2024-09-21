<?php

namespace Tests\Feature;

use App\Services\HttpClientService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\HttpFactory;
use Mockery;
use Mockery\MockInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

/**
 * @property RequestInterface|MockInterface $mockRequest
 * @property ClientInterface|MockInterface $client
 */
class HttpClientServiceTest extends TestCase
{
    public function testSendRequest()
    {
        $this->client->shouldReceive('sendRequest')
            ->with($this->mockRequest)
            ->once()
            ->andReturn($this->mockResponse);

        $this->mockResponse->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode(['Response' => true]));

        $service = new HttpClientService($this->client);

        $response = $service->sendRequest($this->mockRequest);

        $this->assertTrue($response['Response']);
    }

    public function testCreateRequest()
    {
        $method = 'GET';
        $uri = '/test';
        $headers = [
            'Authorization' => 'Bearer token',
            'Content-Type' => 'application/json'
        ];

        $httpFactory = new HttpFactory();

        $partialHttpFactory = Mockery::mock($httpFactory)->makePartial();
        $partialHttpFactory->shouldReceive('createRequest')
            ->with($method, $uri)
            ->andReturn($this->mockRequest);

        $service = new HttpClientService($this->client);
        $request = $service->createRequest($method, $uri, $headers);

        $this->assertEquals($headers['Authorization'], $request->getHeader('Authorization')[0]);
        $this->assertEquals($headers['Content-Type'], $request->getHeader('Content-Type')[0]);
        $this->assertInstanceOf(RequestInterface::class, $request);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->mock(Client::class);
        $this->mockRequest = $this->mock(RequestInterface::class);
        $this->mockResponse = $this->mock(ResponseInterface::class);
    }
}

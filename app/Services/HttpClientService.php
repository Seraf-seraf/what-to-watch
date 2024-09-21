<?php

namespace App\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\RequestInterface;

class HttpClientService
{
    public function __construct(private readonly ClientInterface $httpClient)
    {
    }

    public function sendRequest(RequestInterface $request): array
    {
        $response = $this->httpClient->sendRequest($request);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function createRequest(string $method, string $uri, array $headers = []): RequestInterface
    {
        $request = (new HttpFactory())->createRequest($method, $uri);

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        return $request;
    }
}

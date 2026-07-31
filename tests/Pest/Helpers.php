<?php

namespace Xelon\VmWareClient\Tests\Pest;

use Illuminate\Support\Arr;
use Mockery;

/**
 * Create a mock response with the given data.
 */
function mockResponseData(array $data = [])
{
    $responseBody = json_encode(Arr::wrap($data));
    
    $responseMock = Mockery::mock('Psr\Http\Message\ResponseInterface');
    $responseMock->shouldReceive('getBody')
        ->andReturn($responseBody);
        
    return $responseMock;
}

/**
 * Create a mock client that expects a specific request and returns the given response.
 */
function mockGuzzleClient($expectedMethod, $expectedUri, $responseData = [])
{
    $clientMock = Mockery::mock('GuzzleHttp\Client');
    
    $clientMock->shouldReceive($expectedMethod)
        ->with($expectedUri, Mockery::any())
        ->andReturn(mockResponseData($responseData));
        
    return $clientMock;
}
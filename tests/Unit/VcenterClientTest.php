<?php

use Illuminate\Support\Facades\Cache;
use Xelon\VmWareClient\VcenterClient;
use Xelon\VmWareClient\VcenterSoapClient;
use Xelon\VmWareClient\VmWareClientInit;

test('vcenter client can be instantiated in REST mode', function () {
    // Create a testable mock to avoid actual API calls
    $clientMock = new class('https://vcenter.example.com', 'admin', 'password', VmWareClientInit::MODE_REST) extends VcenterClient {
        // Override to avoid actual REST calls
        protected function initRestSession(): void {
            $this->guzzleClient = new \GuzzleHttp\Client(['verify' => false, 'base_uri' => $this->ip]);
        }
    };
    
    expect($clientMock)->toBeInstanceOf(VcenterClient::class);
    expect($clientMock->apiUrlPrefix)->toBe('/api');
});

test('vcenter client can be instantiated with version 6.7', function () {
    // Create a testable mock to avoid actual API calls
    $clientMock = new class('https://vcenter.example.com', 'admin', 'password', VmWareClientInit::MODE_REST, 6.7) extends VcenterClient {
        // Override to avoid actual REST calls
        protected function initRestSession(): void {
            $this->guzzleClient = new \GuzzleHttp\Client(['verify' => false, 'base_uri' => $this->ip]);
        }
    };
    
    expect($clientMock)->toBeInstanceOf(VcenterClient::class);
    expect($clientMock->apiUrlPrefix)->toBe('/rest');
});

test('vcenter client initializes soap client in both mode', function () {
    $this->markTestSkipped('Skipping test that requires SOAP connection');
});

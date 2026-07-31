<?php

use Illuminate\Support\Facades\Config;
use Xelon\VmWareClient\Facades\VmWareClient;
use Xelon\VmWareClient\VcenterClient;

test('vmware client facade resolves to the correct instance', function () {
    Config::set('vmware-php-client.vcenter_ip', 'https://vcenter.example.com');
    Config::set('vmware-php-client.vcenter_login', 'admin');
    Config::set('vmware-php-client.vcenter_password', 'password');
    Config::set('vmware-php-client.vcenter_mode', 'rest');
    Config::set('vmware-php-client.vcenter_version', 7.0);
    
    // Replace the bound instance with a mock to avoid actual connections
    $this->app->bind('vmware-php-client', function ($app) {
        $mockClient = Mockery::mock(VcenterClient::class);
        
        // Add any stub methods the facade might call
        $mockClient->shouldReceive('getVms')
            ->andReturn([]);
            
        return $mockClient;
    });
    
    // Test the facade resolves correctly
    expect(VmWareClient::getFacadeRoot())->toBeInstanceOf(VcenterClient::class);
    
    // Test a method call through the facade
    $vms = VmWareClient::getVms();
    expect($vms)->toBeArray();
});
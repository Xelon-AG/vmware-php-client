<?php

use Illuminate\Support\Facades\Cache;
use Xelon\VmWareClient\VcenterClient;
use Xelon\VmWareClient\VcenterSoapClient;
use Xelon\VmWareClient\VmWareClientInit;

test('soap client can get vm by name', function () {
    $this->markTestSkipped('Skipping test that requires SOAP connection');
    
    // This test would need more complex mocking to avoid actual SOAP connections
});
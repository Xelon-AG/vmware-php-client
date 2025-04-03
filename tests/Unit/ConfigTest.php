<?php

test('config has required values', function () {
    // Ensure the config file contains all required keys
    $config = config('vmware-php-client');
    
    expect($config)->toBeArray()
        ->toHaveKey('session_ttl')
        ->toHaveKey('enable_logs');
    
    // Test default session_ttl value
    expect($config['session_ttl'])->toBeInt();
});
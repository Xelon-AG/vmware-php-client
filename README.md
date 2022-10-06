
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)
​
# PHP API Client for VMWare
​
[![Latest Version on Packagist](https://img.shields.io/packagist/v/xelon-ag/vmware-php-client.svg?style=flat-square)](https://packagist.org/packages/xelon-ag/vmware-php-client)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/xelon-ag/vmware-php-client/Check%20&%20fix%20styling?label=code%20style)](https://github.com/xelon-ag/vmware-php-client/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/xelon-ag/vmware-php-client.svg?style=flat-square)](https://packagist.org/packages/xelon-ag/vmware-php-client)
​
## Installation
​
You can install the package via composer:
​
```bash
composer require xelon-ag/vmware-php-client
```
​
You can publish the config file with:
​
```bash
php artisan vendor:publish --tag="vmware-php-client-config"
```
​
```php
return [
    'session_ttl' => env('VMWARE_SESSION_TTL', 10),
    'enable_logs' => env('VMWARE_ENABLE_LOGS', true),
];
```
​
​
## Getting started
​
Create a connection to your hypervisor so that you can call the methods:
```php
$vcenterClient = new Xelon\VmWareClient\VcenterClient(
    'https://10.20.30.40', 
    'mylogin', 
    'mypassword'
);
$vmInfo = $vcenterClient->getVmInfo('vm-123');
```
This lib can run in three modes: `rest`, `soap` and `both`. By default, it runs in `rest` mode, but you can set another mode in constructor:
```php
$vcenterClient = new Xelon\VmWareClient\VcenterClient(
    'https://10.20.30.40', 
    'mylogin', 
    'mypassword',
    'soap'
);
```
Yet we recommend to use constants:
```php
$vcenterClient = new Xelon\VmWareClient\VcenterClient(
    'https://10.20.30.40', 
    'mylogin', 
    'mypassword',
    Xelon\VmWareClient\VcenterClient::MODE_SOAP
);
```
​
### `rest` mode
​
With `rest` mode you can use REST methods which you can find in the [VMWare API developer center](https://developer.vmware.com/apis/vsphere-automation/latest/).
For now, the lib has only some methods available. You can find full list of files in the  `vendor/xelon-ag/vmware-php-client/src/Traits/Rest` folder.
​
> We plan to add the full list of methods later.
​
### `soap` mode
​
Using `soap` mode allow you to use SOAP methods which you can find in [VMWare SOAP developer center](https://developer.vmware.com/apis/1192/vsphere).
For now, the lib has only some methods available. You can find full list of files in the `vendor/xelon-ag/vmware-php-client/src/Traits/SOAP` folder.
​
> We plan to add the full list of methods later.
​
Here's how to make your first SOAP call:
```php
$folder = $vcenterClient->soap->createFolder('group-v3', 'foldername');
```
​
If you want to use both modes at one time you can set `both` mode (Xelon\VmWareClient\VcenterClient::MODE_BOTH).
​
If you want to run custom `soap` method, which you do not find in lib, you can run this method directly:
```php
$vcenterClient = new Xelon\VmWareClient\VcenterClient(
    'https://10.20.30.40', 
    'mylogin', 
    'mypassword',
    Xelon\VmWareClient\VcenterClient::MODE_SOAP
);
​
$taskInfo = $vcenterClient->soap->request('ReconfigureComputeResource_Task', [
    '_this' => [
        '_' => 'domain-c33',
        'type' => 'ComputeResource',
    ],
    'spec' => [
        '@type' => 'ClusterConfigSpecEx',
        'drsConfig' => [
            '@type' => 'ClusterDrsConfigInfo',
        ],
        'rulesSpec' => [
            '@type' => 'ClusterRuleSpec',
            'operation' => 'add',
            'info' => [
                '@type' => 'ClusterAntiAffinityRuleSpec',
                'enabled' => true,
                'name' => 'VM-VM Affinity rule',
                'userCreated' => true,
                'vm' => [
                    ['_' => 'vm-133', 'type' => 'VirtualMachine'],
                    ['_' => 'vm-134', 'type' => 'VirtualMachine']
                ]               
            ],
        ],
        'dpmConfig' => [
            '@type' => 'ClusterDpmConfigInfo',
        ],
    
    ],
    'modify' => false,
])
```
​
> Order of parameters is very important. You can find the correct order in the [documentation]((https://developer.vmware.com/apis/1192/vsphere)), the `WSDL type definition` section for each object type.
​
## Credits
​
- [Andrii Hazhur](https://github.com/gazhur94)
- [All Contributors](https://github.com/Xelon-AG/vmware-php-client/graphs/contributors)
  ​
## Questions and feedback
​
If you've got questions about setup or just want to chat with the developer, please feel free to reach out to a.hazhur@bitcat.agency.
​
## License
​
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

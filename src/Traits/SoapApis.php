<?php

namespace Xelon\VmWareClient\Traits;

use Xelon\VmWareClient\Requests\SoapRequest;
use SoapVar;
use Xelon\VmWareClient\Transform\SoapTransform;

trait SoapApis
{
    use SoapRequest;
    use SoapTransform;

    public function reconfigVmTask(string $vmId, array $requestBody)
    {
        return $this->vmRequest('ReconfigVM_Task', $vmId, $requestBody);
    }

    public function addDisk(
        string $vmId,
        int $capacityInKB,
        int $unitNumber,
        bool $isHdd = false,
        string $name = 'New Hard disk'
    ) {
        $body = [
            'spec' => [
                '@type' => 'VirtualMachineConfigSpec',
                'deviceChange' => [
                    '@type' => 'VirtualDeviceConfigSpec',
                    'operation' => 'add',
                    'fileOperation'  => 'create',
                    'device' => [
                        '@type' => 'VirtualDisk',
                        'key' => -101,
                        'deviceInfo' => [
                            '@type' =>  'Description',
                            "label"=> $name,
                            "summary"=> $name
                        ],
                        'backing' => [
                            '@type' => 'VirtualDiskFlatVer2BackingInfo',
                            'fileName' => '',
                            'diskMode' => 'persistent',
                            'thinProvisioned' => false,
                            'eagerlyScrub' => false,
                        ],
                        'controllerKey' => 1000,
                        'unitNumber' => $unitNumber,
                        'capacityInKB' => $capacityInKB,
                        "capacityInBytes" => $capacityInKB * 1024,
                        'storageIOAllocation' => [
                            'limit' => $isHdd ? 3200 : -1,
                            'shares' => [
                                '@type' => 'SharesInfo',
                                'shares' => 1000,
                                'level' => 'normal'
                            ],
                        ],
                    ],
                ],
            ]
        ];

        return $this->reconfigVmTask($vmId, $this->arrayToSoapVar($body));
    }
}

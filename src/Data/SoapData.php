<?php

namespace Xelon\VmWareClient\Data;

class SoapData
{
    public function objectInfoBody(string $objectId, string $objectType, string $pathSet = ''): array
    {
        return [
            '_this' => [
                '_' => 'propertyCollector',
                'type' => 'PropertyCollector',
            ],
            'specSet' => [
                'propSet' => [
                    'type' => $objectType,
                    'all' => ! $pathSet,
                    'pathSet' => $pathSet,
                ],
                'objectSet' => [
                    'obj' => [
                        '_' => $objectId,
                        'type' => $objectType,
                    ],
                    'skip' => false
                ],
            ],
        ];
    }

    public function addVirtualDiskSpec(
        int $capacityInKB,
        int $unitNumber,
        bool $isHdd = false,
        string $name = 'New Hard disk'
    ): array {
        return [
            '@type' => 'VirtualDisk',
            'key' => -101,
            'deviceInfo' => [
                '@type' => 'Description',
                'label' => $name,
                'summary' => $name,
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
            'capacityInBytes' => $capacityInKB * 1024,
            'storageIOAllocation' => [
                'limit' => $isHdd ? 3200 : -1,
                'shares' => [
                    '@type' => 'SharesInfo',
                    'shares' => 1000,
                    'level' => 'normal',
                ],
            ],
        ];
    }

    public function editVirtualDiskSpec(array $params): array
    {
        return [
            '@type' => 'VirtualDisk',
            'key' => $params['key'],
            'backing' => [
                '@type' => 'VirtualDiskFlatVer2BackingInfo',
                'fileName' => $params['backing']['fileName'],
                'diskMode' => $params['backing']['diskMode'] ?? 'persistent',
                'thinProvisioned' => $params['backing']['thinProvisioned'] ?? false,
                'eagerlyScrub' => $params['backing']['eagerlyScrub'] ?? false,
            ],
            'controllerKey' => $params['controllerKey'],
            'unitNumber' => $params['unitNumber'],
            'capacityInKB' => $params['capacityInKB'],
            'capacityInBytes' => $params['capacityInKB'] * 1024,
        ];
    }

    public function addBlockStorageSpec(string $blockStoragePath, int $capacityInKB, int $controllerKey = 1000)
    {
        return [
            '@type' => 'VirtualDisk',
            'key' => -1,
            'backing' => [
                '@type' => 'VirtualDiskFlatVer2BackingInfo',
                'fileName' => $blockStoragePath,
                'diskMode' => 'independent_persistent',
            ],
            'controllerKey' => $controllerKey,
            'unitNumber' => -1,
            'capacityInKB' => $capacityInKB,
        ];
    }

    public function addNetworkSpec(
        string $switchUuid,
        string $portgroupKey,
        int $unitNumber,
        int $controllerKey = 100,
        int $key = -1
    ): array {
        return [
            '@type' => 'VirtualVmxnet3',
            'key' => $key,
            'backing' => [
                '@type' => 'VirtualEthernetCardDistributedVirtualPortBackingInfo',
                'port' => [
                    'switchUuid' => $switchUuid,
                    'portgroupKey' => $portgroupKey,
                ],
            ],
            'controllerKey' => $controllerKey,
            'unitNumber' => $unitNumber,
        ];
    }

    public function editNetworkSpec(
        string $switchUuid,
        string $portgroupKey,
        int $key,
        ?string $macAddress = null
    ): array {
        $data = [
            '@type' => 'VirtualVmxnet3',
            'key' => $key,
            'backing' => [
                '@type' => 'VirtualEthernetCardDistributedVirtualPortBackingInfo',
                'port' => [
                    'switchUuid' => $switchUuid,
                    'portgroupKey' => $portgroupKey,
                ],
            ],
            'addressType' => 'generated',
            'macAddress' => $macAddress,
        ];

        if ($macAddress) {
            $data['macAddress'] = $macAddress;
        }

        return $data;
    }

    public function addSasControllerSpec()
    {
        return [
            '@type' => 'VirtualLsiLogicSASController',
            'busNumber' => 1,
            'hotAddRemove' => true,
            'sharedBus' => 'physicalSharing',
        ];
    }

    public function mountVirtualCdRomSpec(string $fileName, int $key, int $controllerKey, string $datastore): array
    {
        return [
            '@type' => 'VirtualCdrom',
            'key' => $key,
            'backing' => [
                '@type' => 'VirtualCdromIsoBackingInfo',
                'fileName' => $fileName,
                'datastore' => [
                    'type' => 'Datastore',
                    '_' => $datastore,
                ],
            ],
            'connectable' => [
                'startConnected' => true,
                'allowGuestControl' => true,
                'connected' => true,
            ],
            'controllerKey' => $controllerKey,
        ];
    }

    public function unmountVirtualCdRomSpec(int $key, int $controllerKey): array
    {
        return [
            '@type' => 'VirtualCdrom',
            'key' => $key,
            'backing' => [
                '@type' => 'VirtualCdromRemoteAtapiBackingInfo',
                'deviceName' => 'CDRom',
            ],
            'connectable' => [
                'startConnected' => false,
                'allowGuestControl' => false,
                'connected' => false,
            ],
            'controllerKey' => $controllerKey,
        ];
    }

    public function fixedIpAdapterSpec(string $ip, string $subnetMask, array $dnsServerList, array $gateway): array
    {
        return [
            'adapter' => [
                '@type' => 'CustomizationFixedIp',
                'ipAddress' => $ip,
            ],
            'subnetMask' => $subnetMask,
            'dnsServerList' => $dnsServerList,
            'gateway' => $gateway,

        ];
    }

    public function customizationIdendity(string $hostname, string $license, string $password, string $name): array
    {
        return [
            'type' => 'CustomizationSysprep',
            'guiUnattended' => [
                'password' => [
                    'plainText' => true,
                    'value' => $password,
                ],
                'timeZone' => 110,
                'autoLogon' => true,
                'autoLogonCount' => 1,
            ],
            'userData' => [
                'fullName' => $name,
                'orgName' => $name,
                'computerName' => [
                    '@type' => 'CustomizationFixedName',
                    'name' => $hostname,
                ],
                'productId' => $license,

            ],
            'identification' => [
                'joinWorkgroup' => 'workgroup',
            ],
        ];
    }
}

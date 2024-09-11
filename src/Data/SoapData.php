<?php

namespace Xelon\VmWareClient\Data;

use Xelon\VmWareClient\Types\CustomizationAdapterMapping;
use Xelon\VmWareClient\Types\CustomizationFixedIp;
use Xelon\VmWareClient\Types\CustomizationFixedName;
use Xelon\VmWareClient\Types\CustomizationGuiUnattended;
use Xelon\VmWareClient\Types\CustomizationIdentification;
use Xelon\VmWareClient\Types\CustomizationIPSettings;
use Xelon\VmWareClient\Types\CustomizationPassword;
use Xelon\VmWareClient\Types\CustomizationSysprep;
use Xelon\VmWareClient\Types\CustomizationUserData;
use Xelon\VmWareClient\Types\Description;
use Xelon\VmWareClient\Types\DistributedVirtualSwitchPortConnection;
use Xelon\VmWareClient\Types\SharesInfo;
use Xelon\VmWareClient\Types\VirtualCdrom;
use Xelon\VmWareClient\Types\VirtualCdromIsoBackingInfo;
use Xelon\VmWareClient\Types\VirtualCdromRemoteAtapiBackingInfo;
use Xelon\VmWareClient\Types\VirtualDeviceConnectInfo;
use Xelon\VmWareClient\Types\VirtualDisk;
use Xelon\VmWareClient\Types\VirtualDiskFlatVer2BackingInfo;
use Xelon\VmWareClient\Types\VirtualEthernetCardDistributedVirtualPortBackingInfo;
use Xelon\VmWareClient\Types\VirtualLsiLogicSASController;
use Xelon\VmWareClient\Types\VirtualVmxnet3;

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
                    'skip' => false,
                ],
            ],
        ];
    }

    public function addVirtualDiskSpec(
        int $capacityInKB,
        int $unitNumber,
        bool $isHdd = false,
        string $name = 'New Hard disk'
    ): VirtualDisk {
        return new VirtualDisk([
            'key' => -101,
            'deviceInfo' => new Description([
                'label' => $name,
                'summary' => $name,
            ]),
            'backing' => new VirtualDiskFlatVer2BackingInfo([
                'fileName' => '',
                'diskMode' => 'persistent',
                'thinProvisioned' => false,
                'eagerlyScrub' => false,
            ]),
            'controllerKey' => 1000,
            'unitNumber' => $unitNumber,
            'capacityInKB' => $capacityInKB,
            'capacityInBytes' => $capacityInKB * 1024,
            'storageIOAllocation' => [
                'limit' => $isHdd ? 3200 : -1,
                'shares' => new SharesInfo([
                    'shares' => 1000,
                    'level' => 'normal',
                ]),
            ],
        ]);
    }

    public function editVirtualDiskSpec(array $params): VirtualDisk
    {
        return new VirtualDisk([
            'key' => $params['key'],
            'backing' => new VirtualDiskFlatVer2BackingInfo([
                'fileName' => $params['backing']['fileName'],
                'diskMode' => $params['backing']['diskMode'] ?? 'persistent',
                'thinProvisioned' => $params['backing']['thinProvisioned'] ?? false,
                'eagerlyScrub' => $params['backing']['eagerlyScrub'] ?? false,
            ]),
            'controllerKey' => $params['controllerKey'],
            'unitNumber' => $params['unitNumber'],
            'capacityInKB' => $params['capacityInKB'],
            'capacityInBytes' => $params['capacityInKB'] * 1024,
        ]);
    }

    public function addBlockStorageSpec(
        string $blockStoragePath,
        int $capacityInKB,
        int $unitNumber = -1,
        int $controllerKey = 1000
    ): VirtualDisk {
        return new VirtualDisk([
            'key' => -1,
            'backing' => new VirtualDiskFlatVer2BackingInfo([
                'fileName' => $blockStoragePath,
                'diskMode' => 'independent_persistent',
            ]),
            'controllerKey' => $controllerKey,
            'unitNumber' => $unitNumber,
            'capacityInKB' => $capacityInKB,
        ]);
    }

    public function addNetworkSpec(
        string $switchUuid,
        string $portgroupKey,
        int $unitNumber,
        int $controllerKey = 100,
        int $key = -1
    ): VirtualVmxnet3 {
        return new VirtualVmxnet3([
            'key' => $key,
            'backing' => new VirtualEthernetCardDistributedVirtualPortBackingInfo([
                'port' => new DistributedVirtualSwitchPortConnection([
                    'switchUuid' => $switchUuid,
                    'portgroupKey' => $portgroupKey,
                ]),
            ]),
            'controllerKey' => $controllerKey,
            'unitNumber' => $unitNumber,
        ]);
    }

    public function editNetworkSpec(
        string $switchUuid,
        string $portgroupKey,
        int $key,
        ?string $macAddress = null,
        string $addressType = 'generated',
        bool $forceConnected = false,
        bool $startConnected = true,
        bool $connected = true
    ): VirtualVmxnet3 {
        return new VirtualVmxnet3([
            'key' => $key,
            'backing' => new VirtualEthernetCardDistributedVirtualPortBackingInfo([
                'port' => new DistributedVirtualSwitchPortConnection([
                    'switchUuid' => $switchUuid,
                    'portgroupKey' => $portgroupKey,
                ]),
            ]),
            'connectable' => $forceConnected
                ? new VirtualDeviceConnectInfo([
                    'startConnected' => $startConnected,
                    'allowGuestControl' => true,
                    'connected' => $connected,
                ])
                : null,
            'addressType' => $addressType,
            'macAddress' => $macAddress,
        ]);
    }

    public function addSasControllerSpec(): VirtualLsiLogicSASController
    {
        return new VirtualLsiLogicSASController([
            'key' => 1000,
            'busNumber' => 1,
            'hotAddRemove' => true,
            'sharedBus' => 'physicalSharing',
        ]);
    }

    public function mountVirtualCdRomSpec(string $fileName, int $key, int $controllerKey, string $datastore): VirtualCdrom
    {
        return new VirtualCdrom([
            'key' => $key,
            'backing' => new VirtualCdromIsoBackingInfo([
                'fileName' => $fileName,
                'datastore' => [
                    'type' => 'Datastore',
                    '_' => $datastore,
                ],
            ]),
            'connectable' => new VirtualDeviceConnectInfo([
                'startConnected' => true,
                'allowGuestControl' => true,
                'connected' => true,
            ]),
            'controllerKey' => $controllerKey,
        ]);
    }

    public function unmountVirtualCdRomSpec(int $key, int $controllerKey): VirtualCdrom
    {
        return new VirtualCdrom([
            'key' => $key,
            'backing' => new VirtualCdromRemoteAtapiBackingInfo([
                'deviceName' => 'CDRom',
            ]),
            'connectable' => new VirtualDeviceConnectInfo([
                'startConnected' => false,
                'allowGuestControl' => false,
                'connected' => false,
            ]),
            'controllerKey' => $controllerKey,
        ]);
    }

    public function fixedIpAdapterSpec(string $ip, string $subnetMask, array $dnsServerList, array $gateway): CustomizationAdapterMapping
    {
        return new CustomizationAdapterMapping([
            'adapter' => new CustomizationIPSettings([
                'ip' => new CustomizationFixedIp([
                    'ipAddress' => $ip,
                ]),
                'subnetMask' => $subnetMask,
                'dnsServerList' => $dnsServerList,
                'gateway' => $gateway,
            ]),
        ]);
    }

    public function customizationIdendity(string $hostname, ?string $license, ?string $password, string $name): CustomizationSysprep
    {
        return new CustomizationSysprep([
            'guiUnattended' => new CustomizationGuiUnattended([
                'password' => $password
                    ? new CustomizationPassword(['plainText' => true, 'value' => $password])
                    : null,
                'timeZone' => 110,
                'autoLogon' => true,
                'autoLogonCount' => 1,
            ]),
            'userData' => new CustomizationUserData([
                'fullName' => $name,
                'orgName' => $name,
                'computerName' => new CustomizationFixedName([
                    'name' => $hostname,
                ]),
                'productId' => $license,

            ]),
            'identification' => new CustomizationIdentification([
                'joinWorkgroup' => 'workgroup',
            ]),
        ]);
    }
}

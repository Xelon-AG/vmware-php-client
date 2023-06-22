<?php

namespace Xelon\VmWareClient\Traits\Soap;

use Illuminate\Support\Facades\Log;
use Xelon\VmWareClient\Requests\SoapRequest;
use Xelon\VmWareClient\Transform\SoapTransform;
use Xelon\VmWareClient\Types\ClusterAntiAffinityRuleSpec;
use Xelon\VmWareClient\Types\ClusterConfigSpecEx;
use Xelon\VmWareClient\Types\ClusterRuleSpec;
use Xelon\VmWareClient\Types\DVPortgroupConfigSpec;
use Xelon\VmWareClient\Types\DVPortSetting;
use Xelon\VmWareClient\Types\DVSTrafficShapingPolicy;
use Xelon\VmWareClient\Types\VirtualDeviceConfigSpec;
use Xelon\VmWareClient\Types\VirtualDisk;
use Xelon\VmWareClient\Types\VirtualMachineBootOptionsBootableCdromDevice;
use Xelon\VmWareClient\Types\VirtualMachineConfigSpec;

trait SoapVmApis
{
    use SoapRequest;
    use SoapTransform;

    public function getObjectInfo(string $objectId, string $objectType, string $pathSet = '')
    {
        $body = $this->data->objectInfoBody($objectId, $objectType, $pathSet);

        try {
            $result = $this->soapClient->RetrieveProperties($body);
        } catch (\Exception $exception) {
            Log::error(
                "SOAP REQUEST FAILED:\nMessage: ".$exception->getMessage().
                "\nSOAP request: ".($this->soapClient->__getLastRequest() ?? '').
                "\nSOAP response: ".($this->soapClient->__getLastResponse() ?? '')
            );

            if (array_keys(json_decode(json_encode($exception->detail), true))[0] === 'ManagedObjectNotFoundFault') {
                Log::error("404 error, type: $objectType, object id: $objectId");

                return new \stdClass();
            }
        }

        if (! isset($result->returnval->propSet)) {
            Log::error('Get object info error: '.json_encode($result));

            return new \stdClass();
        }

        return $pathSet
            ? ($result->returnval->propSet->val ?? null)
            : $this->transformPropSet($result->returnval->propSet);
    }

    public function getVmInfo(string $vmId, string $pathSet = '')
    {
        if (substr($vmId, 0, 2) !== 'vm') {
            Log::error("Wrong vm id format: $vmId");

            return new \stdClass();
        }

        return $this->getObjectInfo($vmId, 'VirtualMachine', $pathSet);
    }

    public function getTaskInfo(string $taskId, string $pathSet = '')
    {
        return $this->getObjectInfo($taskId, 'Task', $pathSet);
    }

    public function getClusterComputeResourceInfo(string $clusterComputeResourceId, string $pathSet = '')
    {
        return $this->getObjectInfo($clusterComputeResourceId, 'ClusterComputeResource', $pathSet);
    }

    public function getDatastoreInfo(string $datastore, string $pathSet = '')
    {
        return $this->getObjectInfo($datastore, 'Datastore', $pathSet);
    }

    public function getSnapshotInfo(string $taskId, string $pathSet = '')
    {
        return $this->getObjectInfo($taskId, 'VirtualMachineSnapshot', $pathSet);
    }

    public function getTaskHistoryCollectorInfo(string $taskHistoryCollectorId)
    {
        return $this->getObjectInfo($taskHistoryCollectorId, 'TaskHistoryCollector');
    }

    public function getAllVms(string $vmFolder, $pathSet = null)
    {
        $ss1 = new \SoapVar(['name' => 'FolderTraversalSpec'], SOAP_ENC_OBJECT, null, null, 'selectSet', null);
        $ss2 = new \SoapVar(['name' => 'DataCenterVMTraversalSpec'], SOAP_ENC_OBJECT, null, null, 'selectSet', null);
        $a = ['name' => 'FolderTraversalSpec', 'type' => 'Folder', 'path' => 'childEntity', 'skip' => false, $ss1, $ss2];

        $ss = new \SoapVar(['name' => 'FolderTraversalSpec'], SOAP_ENC_OBJECT, null, null, 'selectSet', null);
        $b = ['name' => 'DataCenterVMTraversalSpec', 'type' => 'Datacenter', 'path' => 'vmFolder', 'skip' => false, $ss];

        $body = [
            '_this' => [
                '_' => 'propertyCollector',
                'type' => 'PropertyCollector',
            ],
            'specSet' => [
                'propSet' => [
                    'type' => 'VirtualMachine',
                    'all' => ! $pathSet,
                    'pathSet' => $pathSet,
                ],
                'objectSet' => [
                    'obj' => [
                        '_' => $vmFolder,
                        'type' => 'Folder',
                    ],
                    'skip' => false,
                    'selectSet' => [
                        new \SoapVar($a, SOAP_ENC_OBJECT, 'TraversalSpec'),
                        new \SoapVar($b, SOAP_ENC_OBJECT, 'TraversalSpec'),
                    ],
                ],
            ],
        ];

        $result = $this->soapClient->RetrieveProperties($body);

        return $this->transformPropSetArray($result->returnval ?? []);
    }

    public function getResourcePoolInfo(?string $resourcePoolId, string $pathSet = '')
    {
        if (! $resourcePoolId) {
            return [];
        }

        return $this->getObjectInfo($resourcePoolId, 'ResourcePool', $pathSet);
    }

    public function getDistributedVirtualPortgroupInfo(string $distributedVirtualPortgroupId, string $pathSet = '')
    {
        return $this->getObjectInfo($distributedVirtualPortgroupId, 'DistributedVirtualPortgroup', $pathSet);
    }

    public function reconfigVmTask(string $vmId, array $requestBody)
    {
        return $this->vmRequest('ReconfigVM_Task', $vmId, $this->arrayToSoapVar($requestBody));
    }

    public function cloneVmTask(string $vmId, array $params)
    {
        $body = [
            'folder' => [
                'type' => 'Folder',
                '_' => $params['folder'],
            ],
            'name' => $params['name'],
            'spec' => [
                'location' => [
                    'datastore' => [
                        'type' => 'Datastore',
                        '_' => $params['spec']['location']['datastore'],
                    ],
                    'pool' => (isset($params['spec']['location']['pool']) && $params['spec']['location']['pool'])
                        ? [
                            'type' => 'ResourcePool',
                            '_' => $params['spec']['location']['pool'],
                        ]
                        : null,
                ],
                'template' => $params['spec']['template'] ?? false,
                'config' => isset($params['spec']['config'])
                    ? [
                        'numCPUs' => $params['spec']['config']['numCPUs'],
                        'numCoresPerSocket' => $params['spec']['config']['numCoresPerSocket'],
                        'memoryMB' => $params['spec']['config']['memoryMB'],
                        'deviceChange' => $params['spec']['config']['deviceChange'] ?? null,
                    ]
                    : null,
                'customization' => $params['spec']['customization'] ?? null,
                'powerOn' => $params['spec']['powerOn'] ?? true,
                /*'bootOptions' => [
                    'bootDelay' => $params['spec']['bootOptions']['bootDelay'] ?? 0,
                    'bootRetryEnabled' => $params['spec']['bootOptions']['bootRetryEnabled'] ?? true,
                    'bootOrder' => [
                        '@type' => new VirtualMachineBootOptionsBootableCdromDevice()
                    ]
                ],*/
            ],
        ];

        return $this->vmRequest('CloneVM_Task', $vmId, $body);
    }

    public function addDisk(
        string $vmId,
        int $capacityInKB,
        int $unitNumber,
        bool $isHdd = false,
        string $name = 'New Hard disk'
    ) {
        $body = [
            'spec' => new VirtualMachineConfigSpec([
                'deviceChange' => new VirtualDeviceConfigSpec([
                    'operation' => 'add',
                    'fileOperation' => 'create',
                    'device' => $this->data->addVirtualDiskSpec($capacityInKB, $unitNumber, $isHdd, $name),
                ]),
            ]),
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function editDisk(string $vmId, array $params)
    {
        $body = [
            'spec' => new VirtualMachineConfigSpec([
                'deviceChange' => new VirtualDeviceConfigSpec([
                    'operation' => 'edit',
                    'device' => $this->data->editVirtualDiskSpec($params),
                ]),
            ]),
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function deleteDisk(
        string $vmId,
        string $diskKey,
        bool $deleteFile = true,
        int $capacityInKB = 0,
        int $controllerKey = 1000
    ) {
        $body = [
            'spec' => new VirtualMachineConfigSpec([
                'deviceChange' => new VirtualDeviceConfigSpec([
                    'operation' => 'remove',
                    'fileOperation' => $deleteFile ? 'destroy' : null,
                    'device' => new VirtualDisk([
                        'key' => $diskKey,
                        'capacityInKB' => $capacityInKB,
                        'controllerKey' => $controllerKey,
                    ]),
                ]),
            ]),
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function addPersistantDisk(
        string $vmId,
        string $blockStoragePath,
        int $capacityInKB,
        int $unitNumber = -1,
        int $controllerKey = 1000
    ) {
        $body = [
            'spec' => new VirtualMachineConfigSpec([
                'deviceChange' => new VirtualDeviceConfigSpec([
                    'operation' => 'add',
                    'device' => $this->data->addBlockStorageSpec(
                        $blockStoragePath,
                        $capacityInKB,
                        $unitNumber,
                        $controllerKey
                    ),
                ]),
            ]),
        ];

        return $this->reconfigVmTask($vmId, $this->arrayToSoapVar($body));
    }

    public function addNetwork(string $vmId, int $unitNumber, string $portgroupKey, string $switchUuid)
    {
        $body = [
            'spec' => new VirtualMachineConfigSpec([
                'deviceChange' => new VirtualDeviceConfigSpec([
                    'operation' => 'add',
                    'device' => $this->data->addNetworkSpec($switchUuid, $portgroupKey, $unitNumber),
                ]),
            ]),
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function editNetwork(
        string $vmId,
        string $portgroupKey,
        string $switchUuid,
        string $macAddress,
        int $key,
        string $addressType = 'generated',
        bool $forceConnected = false
    ) {
        $body = [
            'spec' => new VirtualMachineConfigSpec([
                'deviceChange' => new VirtualDeviceConfigSpec([
                    'operation' => 'edit',
                    'device' => $this->data->editNetworkSpec(
                        $switchUuid,
                        $portgroupKey,
                        $key,
                        $macAddress,
                        $addressType,
                        $forceConnected
                    ),
                ]),
            ]),
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function addSasController(string $vmId)
    {
        $body = [
            'spec' => new VirtualMachineConfigSpec([
                'deviceChange' => new VirtualDeviceConfigSpec([
                    'operation' => 'add',
                    'device' => $this->data->addSasControllerSpec(),
                ]),
            ]),
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function changeDVPortgroupSpeed(
        string $distributedVirtualPortgroupId,
        string $configVersion,
        int $speed
    ) {
        $body = [
            '_this' => [
                '_' => $distributedVirtualPortgroupId,
                'type' => 'DistributedVirtualPortgroup',
            ],
            'spec' => new DVPortgroupConfigSpec([
                'configVersion' => $configVersion,
                'defaultPortConfig' => new DVPortSetting([
                    'inShapingPolicy' => new DVSTrafficShapingPolicy([
                        'inherited' => false,
                        'enabled' => [
                            'inherited' => false,
                            'value' => true,
                        ],
                        'averageBandwidth' => [
                            'inherited' => false,
                            'value' => $speed,
                        ],
                        'peakBandwidth' => [
                            'inherited' => false,
                            'value' => $speed,
                        ],
                        'burstSize' => [
                            'inherited' => false,
                            'value' => $speed,
                        ],
                    ]),
                    'outShapingPolicy' => new DVSTrafficShapingPolicy([
                        'inherited' => false,
                        'enabled' => [
                            'inherited' => false,
                            'value' => true,
                        ],
                        'averageBandwidth' => [
                            'inherited' => false,
                            'value' => $speed,
                        ],
                        'peakBandwidth' => [
                            'inherited' => false,
                            'value' => $speed,
                        ],
                        'burstSize' => [
                            'inherited' => false,
                            'value' => $speed,
                        ],
                    ]),
                ]),
            ]),
        ];

        return $this->request('ReconfigureDVPortgroup_Task', $body);
    }

    public function reconfigureComputeResource(
        string $clusterComputerResourceId,
        string $name,
        array $vmIds
    ) {
        $vm = [];

        foreach ($vmIds as $vmId) {
            $vm[] = [
                '_' => $vmId,
                'type' => 'VirtualMachine',
            ];
        }

        $body = [
            '_this' => [
                '_' => $clusterComputerResourceId,
                'type' => 'ComputeResource',
            ],
            'spec' => new ClusterConfigSpecEx([
                // 'drsConfig' => new ClusterDrsConfigInfo(),
                'rulesSpec' => new ClusterRuleSpec([
                    'operation' => 'add',
                    'info' => new ClusterAntiAffinityRuleSpec([
                        'enabled' => true,
                        'name' => $name,
                        'userCreated' => true,
                        'vm' => $vm,
                    ]),
                ]),
                // 'dpmConfig' => new ClusterDpmConfigInfo(),
            ]),
            'modify' => false,
        ];

        return $this->request('ReconfigureComputeResource_Task', $body);
    }

    public function mountIso(string $vmId, string $fileName, int $key, int $controllerKey, string $datastore)
    {
        $body = [
            'spec' => [
                'deviceChange' => [
                    'operation' => 'edit',
                    'device' => $this->data->mountVirtualCdRomSpec($fileName, $key, $controllerKey, $datastore),
                ],
                'bootOptions' => [
                    'bootDelay' => 5000,
                    'bootOrder' => new VirtualMachineBootOptionsBootableCdromDevice(),
                ],
            ],
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function unmountIso(string $vmId, int $key, int $controllerKey)
    {
        $body = [
            'spec' => [
                'deviceChange' => [
                    'operation' => 'edit',
                    'device' => $this->data->unmountVirtualCdRomSpec($key, $controllerKey),
                ],
                'bootOptions' => [
                    'bootDelay' => 0,
                ],
            ],
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function findRulesForVm(string $vmId, string $clusterComputerResource)
    {
        $body = [
            '_this' => [
                '_' => $clusterComputerResource,
                'type' => 'ClusterComputeResource',
            ],
            'vm' => [
                '_' => $vmId,
                'type' => 'VirtualMachine',
            ],
        ];

        return $this->request('FindRulesForVm', $body);
    }

    public function createFolder(string $parentFolder, string $name)
    {
        $body = [
            '_this' => [
                '_' => $parentFolder,
                'type' => 'Folder',
            ],
            'name' => $name,
        ];

        return $this->request('CreateFolder', $body);
    }

    public function deleteFolder(string $folderId)
    {
        $body = [
            '_this' => [
                '_' => $folderId,
                'type' => 'Folder',
            ],
        ];

        return $this->request('Destroy_Task', $body);
    }

    public function deleteVm(string $vmId)
    {
        $body = [
            '_this' => [
                '_' => $vmId,
                'type' => 'VirtualMachine',
            ],
        ];

        return $this->request('Destroy_Task', $body);
    }

    public function createSnapshot(
        string $vmId,
        string $name,
        ?string $description,
        bool $memory = false,
        bool $quiesce = true
    ) {
        $body = [
            'name' => $name,
            'description' => $description,
            'memory' => $memory,
            'quiesce' => $quiesce,
        ];

        return $this->vmRequest('CreateSnapshot_Task', $vmId, $body);
    }

    public function revertSnapshot(string $snapshopId)
    {
        $body = [
            '_this' => [
                '_' => $snapshopId,
                'type' => 'VirtualMachineSnapshot',
            ],
        ];

        return $this->request('RevertToSnapshot_Task', $body);
    }

    public function removeSnapshot(string $snapshopId, bool $removeChildren = true, bool $consolidate = true)
    {
        $body = [
            '_this' => [
                '_' => $snapshopId,
                'type' => 'VirtualMachineSnapshot',
            ],
            'removeChildren' => $removeChildren,
            'consolidate' => $consolidate,
        ];

        return $this->request('RemoveSnapshot_Task', $body);
    }

    public function queryPerf(
        string $objectId,
        ?string $startTime = null,
        ?string $endTime = null,
        int $intervalId = 20,
        ?int $maxSample = null,
        array $metricIds = [],
        string $entity = 'VirtualMachine'
    ) {
        $body = [
            '_this' => [
                '_' => 'PerfMgr',
                'type' => 'PerformanceManager',
            ],
            'querySpec' => [
                'entity' => [
                    '_' => $objectId,
                    'type' => $entity,
                ],
                'startTime' => $startTime,
                'endTime' => $endTime,
                'maxSample' => $maxSample,
                'metricId' => array_map(fn (int $id): array => ['counterId' => $id, 'instance' => ''], $metricIds),
                'intervalId' => $intervalId,
                'format' => 'normal',
            ],
        ];

        return $this->transformToArrayValues($this->request('QueryPerf', $body, false));
    }

    public function acquireTicket(string $vmId, string $ticketType = 'webmks')
    {
        return $this->vmRequest('AcquireTicket', $vmId, ['ticketType' => $ticketType]);
    }

    public function mountToolsInstaller(string $vmId)
    {
        return $this->vmRequest('MountToolsInstaller', $vmId);
    }

    public function unmountToolsInstaller(string $vmId)
    {
        return $this->vmRequest('UnmountToolsInstaller', $vmId);
    }

    public function consolidateVmDisks(string $vmId)
    {
        $body = [
            '_this' => [
                'type' => 'VirtualMachine',
                '_' => $vmId,
            ],
        ];

        return $this->request('ConsolidateVMDisks_Task', $body);
    }
}

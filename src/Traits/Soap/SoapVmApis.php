<?php

namespace Xelon\VmWareClient\Traits\Soap;

use Xelon\VmWareClient\Requests\SoapRequest;
use Xelon\VmWareClient\Transform\SoapTransform;

trait SoapVmApis
{
    use SoapRequest;
    use SoapTransform;

    public function getObjectInfo(string $objectId, string $objectType, string $pathSet = '')
    {
        $body = $this->data->objectInfoBody($objectId, $objectType, $pathSet);

        $result = $this->soapClient->RetrieveProperties($body);

        return $pathSet
            ? ($result->returnval->propSet->val ?? null)
            : $this->transformPropSet($result->returnval->propSet);
    }

    public function getVmInfo(string $vmId, string $pathSet = '')
    {
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
                    'pool' => [
                        'type' => 'ResourcePool',
                        '_' => $params['spec']['location']['pool'],
                    ],
                ],
                'template' => $params['spec']['template'] ?? false,
                'config' => [
                    'numCPUs' => $params['spec']['config']['numCPUs'],
                    'numCoresPerSocket' => $params['spec']['config']['numCoresPerSocket'],
                    'memoryMB' => $params['spec']['config']['memoryMB'],
                    'deviceChange' => $params['spec']['config']['deviceChange'] ?? null,
                ],
                'customization' => $params['spec']['customization'] ?? null,
                'powerOn' => $params['spec']['powerOn'] ?? true,
                /*'bootOptions' => [
                    'bootDelay' => $params['spec']['bootOptions']['bootDelay'] ?? 0,
                    'bootRetryEnabled' => $params['spec']['bootOptions']['bootRetryEnabled'] ?? true,
                    'bootOrder' => [
                        '@type' => 'VirtualMachineBootOptionsBootableCdromDevice'
                    ]
                ],*/
            ],
        ];

        return $this->vmRequest('CloneVM_Task', $vmId, $this->arrayToSoapVar($body));
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
                    'fileOperation' => 'create',
                    'device' => $this->data->addVirtualDiskSpec($capacityInKB, $unitNumber, $isHdd, $name),
                ],
            ],
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function editDisk(string $vmId, array $params)
    {
        $body = [
            'spec' => [
                '@type' => 'VirtualMachineConfigSpec',
                'deviceChange' => [
                    '@type' => 'VirtualDeviceConfigSpec',
                    'operation' => 'edit',
                    'device' => $this->data->editVirtualDiskSpec($params),
                ],
            ],
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function addNetwork(string $vmId, int $unitNumber, string $portgroupKey, string $switchUuid)
    {
        $body = [
            'spec' => [
                '@type' => 'VirtualMachineConfigSpec',
                'deviceChange' => [
                    '@type' => 'VirtualDeviceConfigSpec',
                    'operation' => 'add',
                    'device' => $this->data->addNetworkSpec($switchUuid, $portgroupKey, $unitNumber),
                ],
            ],
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function editNetwork(
        string $vmId,
        string $portgroupKey,
        string $switchUuid,
        string $macAddress,
        int $key
    ) {
        $body = [
            'spec' => [
                '@type' => 'VirtualMachineConfigSpec',
                'deviceChange' => [
                    '@type' => 'VirtualDeviceConfigSpec',
                    'operation' => 'edit',
                    'device' => $this->data->editNetworkSpec($switchUuid, $portgroupKey, $key, $macAddress),
                ],
            ],
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function addSasController(string $vmId)
    {
        $body = [
            'spec' => [
                '@type' => 'VirtualMachineConfigSpec',
                'deviceChange' => [
                    '@type' => 'VirtualLsiLogicSASController',
                    'operation' => 'add',
                    'device' => $this->data->addSasControllerSpec(),
                ],
            ],
        ];

        return $this->reconfigVmTask($vmId, $body);
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
                    'bootOrder' => [
                        '@type' => 'VirtualMachineBootOptionsBootableCdromDevice',
                    ],
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

        return $this->soapClient->FindRulesForVm($body);
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

        return $this->soapClient->CreateFolder($body);
    }

    public function createSnapshot(
        string $vmId,
        string $name,
        string $description,
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

        return $this->soapClient->RevertToSnapshot_Task($body);
    }

    public function removeSnapshot(string $snapshopId, bool $removeChildren = true, bool $consolidate = true)
    {
        $body = [
            '_this' => [
                '_' => $snapshopId,
                'type' => 'VirtualMachineSnapshot',
            ],
            'removeChildren' => $removeChildren,
            '$consolidate' => $consolidate,
        ];

        return $this->soapClient->RemoveSnapshot_Task($body);
    }

    public function queryPerf(
        string $vmId,
        ?string $startTime = null,
        ?string $endTime = null,
        int $intervalId = 20,
        ?int $maxSample = null,
        array $metricIds = []
    ) {
        $body = [
            '_this' => [
                '_' => 'PerfMgr',
                'type' => 'PerformanceManager',
            ],
            'querySpec' => [
                'entity' => [
                    '_' => $vmId,
                    'type' => 'VirtualMachine',
                ],
                'startTime' => $startTime,
                'endTime' => $endTime,
                'maxSample' => $maxSample,
                'metricId' => array_map(fn (int $id): array => ['counterId' => $id, 'instance' => ''], $metricIds),
                'intervalId' => $intervalId,
                'format' => 'normal',
            ],
        ];

        return $this->soapClient->QueryPerf($body);
    }
}
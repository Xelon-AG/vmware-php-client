<?php

namespace Xelon\VmWareClient\Traits\Soap;

use Xelon\VmWareClient\Requests\SoapRequest;
use Xelon\VmWareClient\Transform\SoapTransform;

trait SoapVmApis
{
    use SoapRequest;
    use SoapTransform;

    public function getVmInfo(string $vmId, string $pathSet = '')
    {
        $body = [
            '_this' => [
                '_' => 'propertyCollector',
                'type' => 'PropertyCollector'
            ],
            'specSet' => [
                'propSet' => [
                    'type' => 'VirtualMachine',
                    'all' => !$pathSet,
                    'pathSet' => $pathSet
                ],
                'objectSet' => [
                    'obj' => [
                        '_' => $vmId,
                        'type' => 'VirtualMachine'
                    ]
                ]
            ]
        ];

        $result = $this->soapClient->RetrieveProperties($body);

        return $pathSet
            ? ($result->returnval->propSet->val ?? null)
            : $this->transformPropSet($result->returnval->propSet);
    }

    public function getTaskInfo(string $taskId, string $pathSet = '')
    {
        $body = [
            '_this' => [
                '_' => 'propertyCollector',
                'type' => 'PropertyCollector'
            ],
            'specSet' => [
                'propSet' => [
                    'type' => 'Task',
                    'all' => !$pathSet,
                    'pathSet' => $pathSet
                ],
                'objectSet' => [
                    'obj' => [
                        '_' => $taskId,
                        'type' => 'Task'
                    ]
                ]
            ]
        ];

        $result = $this->soapClient->RetrieveProperties($body);

        return $pathSet
            ? ($result->returnval->propSet->val ?? null)
            : $this->transformPropSet($result->returnval->propSet);
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
                '_' => $params['folder']
            ],
            'name' => $params['name'],
            'spec' => [
                'location' => [
                    'datastore' => [
                        'type' => 'Datastore',
                        '_' => $params['spec']['location']['datastore']
                    ],
                    'pool' => [
                        'type' => 'ResourcePool',
                        '_' => $params['spec']['location']['pool']
                    ]
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
            ]
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
                    'device' => $this->data->addVirtualDiskSpec($capacityInKB, $unitNumber, $isHdd, $name)
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
                    'device' => $this->data->editVirtualDiskSpec($params)
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
                        '@type' => 'VirtualMachineBootOptionsBootableCdromDevice'
                    ]
                ],
            ]
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
            ]
        ];

        return $this->reconfigVmTask($vmId, $body);
    }

    public function findRulesForVm(string $vmId, string $clusterComputerResource)
    {
        $body = [
            '_this' => [
                '_' => $clusterComputerResource,
                'type' => 'ClusterComputeResource'
            ],
            'vm' => [
                '_' => $vmId,
                'type' => 'VirtualMachine',
            ]
        ];

        return $this->soapClient->FindRulesForVm($body);
    }

    public function createFolder(string $parentFolder, string $name)
    {
        $body = [
            '_this' => [
                '_' => $parentFolder,
                'type' => 'Folder'
            ],
            'name' => $name
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
            'quiesce' => $quiesce
        ];

        return $this->vmRequest('CreateSnapshot_Task', $vmId, $body);
    }
}

<?php

namespace Xelon\VmWareClient\Traits\Soap;

use Xelon\VmWareClient\Requests\SoapRequest;
use Xelon\VmWareClient\Transform\SoapTransform;
use Xelon\VmWareClient\Types\VslmCreateSpecDiskFileBackingSpec;

trait SoapStorageApis
{
    use SoapRequest;
    use SoapTransform;

    public function getVcenterVStorageInfo(string $vstorageId, string $datastore)
    {
        $body = [
            'id' => [
                'id' => $vstorageId,
            ],
            '_this' => [
                'type' => 'VcenterVStorageObjectManager',
                '_' => 'VStorageObjectManager',
            ],
            'datastore' => [
                'type' => 'Datastore',
                '_' => $datastore,
            ],
        ];

        return $this->transformToArrayValues($this->request('RetrieveVStorageObject', $body));
    }

    public function deleteVcenterVStorageInfo(string $vstorageId, string $datastore)
    {
        $body = [
            'id' => [
                'id' => $vstorageId,
            ],
            '_this' => [
                'type' => 'VcenterVStorageObjectManager',
                '_' => 'VStorageObjectManager',
            ],
            'datastore' => [
                'type' => 'Datastore',
                '_' => $datastore,
            ],
        ];

        return $this->request('DeleteVStorageObject_Task', $body);
    }

    public function createVStorage(string $name, int $capacityInMB, string $datastore, bool $keepAfterDeleteVm = true)
    {
        $body = [
            '_this' => [
                'type' => 'VcenterVStorageObjectManager',
                '_' => 'VStorageObjectManager',
            ],
            'spec' => [
                'name' => $name,
                'keepAfterDeleteVm' => $keepAfterDeleteVm,
                'backingSpec' => new VslmCreateSpecDiskFileBackingSpec([
                    'datastore' => [
                        'type' => 'Datastore',
                        '_' => $datastore,
                    ],
                ]),
                'capacityInMB' => $capacityInMB,
            ],
        ];

        return $this->request('CreateDisk_Task', $body);
    }

    public function extendVStorage(string $vstorageId, string $datastore, int $newCapacityInMB)
    {
        $body = [
            '_this' => [
                'type' => 'VcenterVStorageObjectManager',
                '_' => 'VStorageObjectManager',
            ],
            'id' => [
                'id' => $vstorageId,
            ],
            'datastore' => [
                'type' => 'Datastore',
                '_' => $datastore,
            ],
            'newCapacityInMB' => $newCapacityInMB,
        ];

        return $this->request('ExtendDisk_Task', $body);
    }
}

<?php

namespace Xelon\VmWareClient\Traits\Soap;

use Xelon\VmWareClient\Requests\SoapRequest;
use Xelon\VmWareClient\Transform\SoapTransform;

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

        return $this->soapClient->RetrieveVStorageObject($this->arrayToSoapVar($body));
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

        return $this->soapClient->DeleteVStorageObject_Task($this->arrayToSoapVar($body));
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
                'backingSpec' => [
                    '@type' => 'VslmCreateSpecDiskFileBackingSpec',
                    'datastore' => [
                        'type' => 'Datastore',
                        '_' => $datastore,
                    ],
                ],
                'capacityInMB' => $capacityInMB,
            ],
        ];

        return $this->soapClient->CreateDisk_Task($this->arrayToSoapVar($body));
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

        return $this->soapClient->ExtendDisk_Task($this->arrayToSoapVar($body));
    }
}

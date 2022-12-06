<?php

namespace Xelon\VmWareClient\Traits\Rest;

use Xelon\VmWareClient\Requests\ApiRequest;
use Xelon\VmWareClient\Transform\SoapTransform;

trait VcenterApis
{
    use ApiRequest;
    use SoapTransform;

    public function createVm()
    {
        // TODO:
    }

    public function getVmList(array $requestBody = [])
    {
        return $this->request(
            'get',
            "$this->apiUrlPrefix/vcenter/vm",
            ['query' => $this->getListFilterQuery($requestBody)]
        );
    }

    public function getVmInfo(string $vmId)
    {
        $result = $this->request('get', "$this->apiUrlPrefix/vcenter/vm/$vmId");

        if ($this->version < 7 && isset($result->disks)) {
            foreach ($result->disks as $disk) {
                foreach ($disk->value as $key => $property) {
                    $disk->$key = $property;
                }
            }
        }

        return $result;
    }

    public function deleteVm(string $vmId)
    {
        return $this->request('delete', "$this->apiUrlPrefix/vcenter/vm/$vmId");
    }

    public function cloneVm(array $requestBody)
    {
        return $this->request('post', "$this->apiUrlPrefix/vcenter/vm?action=clone", ['json' => $requestBody]);
    }

    public function registerVm()
    {
        // TODO:
    }

    public function relocateVm()
    {
        // TODO:
    }

    public function instantCloneVm()
    {
        // TODO:
    }

    public function unregisterVm()
    {
        // TODO:
    }

    public function getNetworkList(array $requestBody = [])
    {
        return $this->request(
            'get',
            "$this->apiUrlPrefix/vcenter/network",
            ['query' => $this->getListFilterQuery($requestBody)]
        );
    }
}

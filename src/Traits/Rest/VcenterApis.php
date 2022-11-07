<?php

namespace Xelon\VmWareClient\Traits\Rest;

use Xelon\VmWareClient\Requests\ApiRequest;

trait VcenterApis
{
    use ApiRequest;

    public function createVm()
    {
        // TODO:
    }

    public function getVmList(array $requestBody = [])
    {
        $query = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($requestBody, null, '&'));

        return $this->request('get', "$this->apiUrlPrefix/vcenter/vm", ['query' => $query]);
    }

    public function getVmInfo(string $vmId)
    {
        return $this->request('get', "$this->apiUrlPrefix/vcenter/vm/$vmId");
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
        $query = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($requestBody, null, '&'));

        return $this->request('get', "$this->apiUrlPrefix/vcenter/network", ['query' => $query]);
    }
}

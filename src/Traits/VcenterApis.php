<?php

namespace Xelon\VmWareClient\Traits;

use Xelon\VmWareClient\Requests\ApiRequest;

trait VcenterApis
{
    use ApiRequest;

    public function createVm()
    {
        // TODO:
    }

    public function getVmList()
    {
        return $this->request('get', '/api/vcenter/vm');
    }

    public function getVmInfo(string $vmId)
    {
        return $this->request('get', "/api/vcenter/vm/$vmId");
    }

    public function deleteVm()
    {
        // TODO:
    }

    public function cloneVm(array $requestBody)
    {
        return $this->request('post', '/api/vcenter/vm?action=clone', ['body' => $requestBody]);
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
}

<?php

namespace Xelon\VmWareClient\Traits;

use Xelon\VmWareClient\Requests\SoapRequest;

trait SoapApis
{
    use SoapRequest;

    public function reconfigVmTask(string $vmId, array $requestBody)
    {
        $this->vmRequest('ReconfigVM_Task', $vmId, $requestBody);
    }
}

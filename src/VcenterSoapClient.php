<?php

namespace Xelon\VmWareClient;

use SoapClient;
use Xelon\VmWareClient\Data\SoapData;
use Xelon\VmWareClient\Traits\Soap\SoapGuestApis;
use Xelon\VmWareClient\Traits\Soap\SoapStorageApis;
use Xelon\VmWareClient\Traits\Soap\SoapVmApis;

class VcenterSoapClient
{
    use SoapVmApis;
    use SoapGuestApis;
    use SoapStorageApis;

    public SoapClient $soapClient;

    public SoapData $data;

    public function __construct(SoapClient $soapClient)
    {
        $this->soapClient = $soapClient;
        $this->data = new SoapData();
    }
}

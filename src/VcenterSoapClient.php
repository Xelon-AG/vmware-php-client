<?php

namespace Xelon\VmWareClient;

use SoapClient;
use Xelon\VmWareClient\Data\SoapData;
use Xelon\VmWareClient\Traits\Soap\SoapFileApis;
use Xelon\VmWareClient\Traits\Soap\SoapVmApis;

class VcenterSoapClient
{
    use SoapVmApis;
    use SoapFileApis;

    public SoapClient $soapClient;

    public SoapData $data;

    public function __construct(SoapClient $soapClient)
    {
        $this->soapClient = $soapClient;
        $this->data = new SoapData();
    }
}

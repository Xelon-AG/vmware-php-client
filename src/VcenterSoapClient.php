<?php

namespace Xelon\VmWareClient;

use SoapClient;
use Xelon\VmWareClient\Data\SoapData;
use Xelon\VmWareClient\Requests\SoapRequest;
use Xelon\VmWareClient\Traits\Soap\SoapGuestApis;
use Xelon\VmWareClient\Traits\Soap\SoapImportApis;
use Xelon\VmWareClient\Traits\Soap\SoapStorageApis;
use Xelon\VmWareClient\Traits\Soap\SoapVmApis;

class VcenterSoapClient
{
    use SoapRequest;
    use SoapVmApis;
    use SoapGuestApis;
    use SoapImportApis;
    use SoapStorageApis;

    public SoapClient $soapClient;

    public SoapData $data;

    public string $ip;

    public string $login;

    public string $password;

    public function __construct(SoapClient $soapClient, string $ip, string $login, string $password)
    {
        $this->soapClient = $soapClient;
        $this->ip = $ip;
        $this->login = $login;
        $this->password = $password;
        $this->data = new SoapData();
    }
}

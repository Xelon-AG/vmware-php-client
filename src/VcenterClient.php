<?php

namespace Xelon\VmWareClient;

use Xelon\VmWareClient\Traits\Rest\CisApis;
use Xelon\VmWareClient\Traits\Rest\IsoApis;
use Xelon\VmWareClient\Traits\Rest\OfvApis;
use Xelon\VmWareClient\Traits\Rest\VcenterApis;
use Xelon\VmWareClient\Traits\Rest\VmApis;

class VcenterClient extends VmWareClientInit
{
    use VcenterApis;
    use VmApis;
    use IsoApis;
    use CisApis;
    use OfvApis;

    public ?VcenterSoapClient $soap;

    public function __construct(string $ip, string $login, string $password, string $mode = self::MODE_REST)
    {
        parent::__construct($ip, $login, $password, $mode);

        if ($mode === self::MODE_SOAP || $mode === self::MODE_BOTH) {
            $this->soap = new VcenterSoapClient($this->soapClient);
        }
    }
}

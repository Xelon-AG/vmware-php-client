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

    public string $apiUrlPrefix;

    public ?VcenterSoapClient $soap;

    public function __construct(
        string $ip,
        string $login,
        string $password,
        string $mode = self::MODE_REST,
        float $version = 7
    ) {
        parent::__construct($ip, $login, $password, $mode, $version);

        if ($mode === self::MODE_SOAP || $mode === self::MODE_BOTH) {
            $this->soap = new VcenterSoapClient($this->soapClient);
        }

        $this->apiUrlPrefix = $this->version >= 7 ? '/api' : '/rest';
    }
}

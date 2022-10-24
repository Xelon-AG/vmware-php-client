<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class CustomizationIPSettings extends DynamicData
{
    public $ip;

    public $subnetMask;

    public $gateway;

    public $ipV6Spec;

    public $dnsServerList;

    public $dnsDomain;

    public $primaryWINS;

    public $secondaryWINS;

    public $netBIOS;
}

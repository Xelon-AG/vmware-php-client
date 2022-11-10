<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class DVPortSetting extends DynamicData
{
    public $blocked;

    public $vmDirectPathGen2Allowed;

    public $inShapingPolicy;

    public $outShapingPolicy;

    public $vendorSpecificConfig;

    public $networkResourcePoolKey;

    public $filterPolicy;
}

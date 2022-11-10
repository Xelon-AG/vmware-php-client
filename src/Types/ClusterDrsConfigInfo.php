<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class ClusterDrsConfigInfo extends DynamicData
{
    public $enabled;

    public $enableVmBehaviorOverrides;

    public $defaultVmBehavior;

    public $vmotionRate;

    public $scaleDescendantsShares;

    public $option;
}

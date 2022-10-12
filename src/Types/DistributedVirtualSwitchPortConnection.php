<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class DistributedVirtualSwitchPortConnection extends DynamicData
{
    public $switchUuid;

    public $portgroupKey;

    public $portKey;

    public $connectionCookie;
}

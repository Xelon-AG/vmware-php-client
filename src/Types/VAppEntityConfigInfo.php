<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class VAppEntityConfigInfo extends DynamicData
{
    public $key;

    public $tag;

    public $startOrder;

    public $startDelay;

    public $waitingForGuest;

    public $startAction;

    public $stopDelay;

    public $stopAction;

    public $destroyWithParent;
}

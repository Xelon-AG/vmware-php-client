<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class VirtualDevice extends DynamicData
{
    public $key;

    public $deviceInfo;

    public $backing;

    public $connectable;

    public $slotInfo;

    public $controllerKey;

    public $unitNumber;
}

<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class VirtualDeviceConfigSpec extends DynamicData
{
    public $operation;

    public $fileOperation;

    public $device;

    public $profile;

    public $backing;
}

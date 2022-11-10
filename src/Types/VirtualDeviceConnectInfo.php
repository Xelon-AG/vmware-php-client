<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class VirtualDeviceConnectInfo extends DynamicData
{
    public $migrateConnect;

    public $startConnected;

    public $allowGuestControl;

    public $connected;

    public $status;
}

<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class VirtualMachineFileInfo extends DynamicData
{
    public $vmPathName;

    public $snapshotDirectory;

    public $suspendDirectory;

    public $logDirectory;

    public $ftMetadataDirectory;
}

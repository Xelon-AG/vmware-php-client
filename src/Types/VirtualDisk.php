<?php

namespace Xelon\VmWareClient\Types;

class VirtualDisk extends VirtualDevice
{
    public $capacityInKB;

    public $capacityInBytes;

    public $shares;

    public $storageIOAllocation;

    public $diskObjectId;

    public $vFlashCacheConfigInfo;

    public $iofilter;

    public $vDiskId;

    public $nativeUnmanagedLinkedClone;

    public $independentFilters;
}

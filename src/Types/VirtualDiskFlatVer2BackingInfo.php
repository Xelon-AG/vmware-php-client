<?php

namespace Xelon\VmWareClient\Types;

class VirtualDiskFlatVer2BackingInfo extends VirtualDeviceFileBackingInfo
{
    public $diskMode;

    public $split;

    public $writeThrough;

    public $thinProvisioned;

    public $eagerlyScrub;

    public $uuid;

    public $contentId;

    public $changeId;

    public $parent;

    public $deltaDiskFormat;

    public $digestEnabled;

    public $deltaGrainSize;

    public $deltaDiskFormatVariant;

    public $sharing;

    public $keyId;
}

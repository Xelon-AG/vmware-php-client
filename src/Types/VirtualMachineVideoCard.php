<?php

namespace Xelon\VmWareClient\Types;

class VirtualMachineVideoCard extends VirtualDevice
{
    public $videoRamSizeInKB;

    public $numDisplays;

    public $useAutoDetect;

    public $enable3DSupport;

    public $use3dRenderer;

    public $graphicsMemorySizeInKB;
}

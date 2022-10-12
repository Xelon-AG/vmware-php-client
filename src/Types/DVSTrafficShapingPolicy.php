<?php

namespace Xelon\VmWareClient\Types;

class DVSTrafficShapingPolicy extends InheritablePolicy
{
    public $enabled;

    public $averageBandwidth;

    public $peakBandwidth;

    public $burstSize;
}

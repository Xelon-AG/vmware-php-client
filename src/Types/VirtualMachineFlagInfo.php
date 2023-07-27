<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class VirtualMachineFlagInfo extends DynamicData
{
    public $disableAcceleration;

    public $enableLogging;

    public $useToe;

    public $runWithDebugInfo;

    public $monitorType;

    public $htSharing;

    public $snapshotDisabled;

    public $snapshotLocked;

    public $diskUuidEnabled;

    public $virtualMmuUsage;

    public $virtualExecUsage;

    public $snapshotPowerOffBehavior;

    public $recordReplayEnabled;

    public $faultToleranceType;

    public $cbrcCacheEnabled;

    public $vvtdEnabled;

    public $vbsEnabled;
}
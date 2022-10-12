<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class VirtualMachineConfigSpec extends DynamicData
{
    public $changeVersion;

    public $name;

    public $version;

    public $createDate;

    public $uuid;

    public $instanceUuid;

    public $npivNodeWorldWideName;

    public $npivPortWorldWideName;

    public $npivWorldWideNameType;

    public $npivDesiredNodeWwns;

    public $npivDesiredPortWwns;

    public $npivTemporaryDisabled;

    public $npivOnNonRdmDisks;

    public $npivWorldWideNameOp;

    public $locationId;

    public $guestId;

    public $alternateGuestName;

    public $annotation;

    public $files;

    public $tools;

    public $flags;

    public $consolePreferences;

    public $powerOpInfo;

    public $numCPUs;

    public $vcpuConfig;

    public $numCoresPerSocket;

    public $memoryMB;

    public $memoryHotAddEnabled;

    public $cpuHotAddEnabled;

    public $cpuHotRemoveEnabled;

    public $virtualICH7MPresent;

    public $virtualSMCPresent;

    public $deviceChange;

    public $cpuAllocation;

    public $memoryAllocation;

    public $latencySensitivity;

    public $cpuAffinity;

    public $memoryAffinity;

    public $networkShaper;

    public $cpuFeatureMask;

    public $extraConfig;

    public $swapPlacement;

    public $bootOptions;

    public $vAppConfig;

    public $ftInfo;

    public $repConfig;

    public $vAppConfigRemoved;

    public $vAssertsEnabled;

    public $changeTrackingEnabled;

    public $firmware;

    public $maxMksConnections;

    public $guestAutoLockEnabled;

    public $managedBy;

    public $memoryReservationLockedToMax;

    public $nestedHVEnabled;

    public $vPMCEnabled;

    public $scheduledHardwareUpgradeInfo;

    public $vmProfile;

    public $messageBusTunnelEnabled;

    public $crypto;

    public $migrateEncryption;

    public $sgxInfo;

    public $guestMonitoringModeInfo;
}

<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class DVPortgroupConfigSpec extends DynamicData
{
    public $configVersion;

    public $name;

    public $numPorts;

    public $portNameFormat;

    public $defaultPortConfig;

    public $description;

    public $type;

    public $backingType;

    public $scope;

    public $policy;

    public $vendorSpecificConfig;

    public $autoExpand;

    public $vmVnicNetworkResourcePoolKey;

    public $transportZoneUuid;

    public $transportZoneName;

    public $logicalSwitchUuid;

    public $segmentId;

}

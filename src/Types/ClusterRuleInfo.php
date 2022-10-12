<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class ClusterRuleInfo extends DynamicData
{
    public $key;

    public $status;

    public $enabled;

    public $name;

    public $mandatory;

    public $userCreated;

    public $inCompliance;

    public $ruleUuid;
}

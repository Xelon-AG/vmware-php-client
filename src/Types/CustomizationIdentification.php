<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class CustomizationIdentification extends DynamicData
{
    public $joinWorkgroup;

    public $joinDomain;

    public $domainAdmin;

    public $domainAdminPassword;
}

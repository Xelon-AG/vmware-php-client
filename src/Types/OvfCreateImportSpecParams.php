<?php

namespace Xelon\VmWareClient\Types;

class OvfCreateImportSpecParams extends OvfManagerCommonParams
{
    public $entityName;

    public $hostSystem;

    public $networkMapping;

    public $ipAllocationPolicy;

    public $ipProtocol;

    public $propertyMapping;

    public $resourceMapping;

    public $diskProvisioning;

    public $instantiationOst;
}

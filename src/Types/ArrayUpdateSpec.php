<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class ArrayUpdateSpec extends DynamicData
{
    public $operation;

    public $removeKey;
}

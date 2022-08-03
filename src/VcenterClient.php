<?php

namespace Xelon\VmWareClient;

use Xelon\VmWareClient\Traits\SoapApis;
use Xelon\VmWareClient\Traits\VcenterApis;
use Xelon\VmWareClient\Traits\VmApis;

class VcenterClient extends VmWareClientInit
{
    use VcenterApis;
    use VmApis;
    use SoapApis;
}

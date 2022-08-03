<?php

namespace Xelon\VmWareClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Xelon\VmWareClient\VmWareClientInit
 */
class VmWareClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'vmware-php-client';
    }
}

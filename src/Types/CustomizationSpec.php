<?php

namespace Xelon\VmWareClient\Types;

use Xelon\VmWareClient\Types\Core\DynamicData;

class CustomizationSpec extends DynamicData
{
    public $options;

    public $identity;

    public $globalIPSettings;

    public $nicSettingMap;

    public $encryptionKey;
}

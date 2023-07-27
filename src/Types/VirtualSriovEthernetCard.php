<?php

namespace Xelon\VmWareClient\Types;

class VirtualSriovEthernetCard extends VirtualEthernetCard
{
    public $allowGuestOSMtuChange;

    public $sriovBacking;
}
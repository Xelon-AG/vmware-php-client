<?php

namespace Xelon\VmWareClient\Traits\Rest;

use Xelon\VmWareClient\Requests\ApiRequest;

trait IsoApis
{
    use ApiRequest;

    public function mountImage(string $vmId, string $libraryItem)
    {
        return $this->request('post', '/api/vcenter/iso/image?action=mount', ['form_params' => [
            'library_item' => $libraryItem,
            'vm' => $vmId,
        ]]);
    }

    public function unmountImage(string $vmId, string $cdRom)
    {
        return $this->request('post', '/api/vcenter/iso/image?action=unmount', ['form_params' => [
            'cdrom' => $cdRom,
            'vm' => $vmId,
        ]]);
    }
}

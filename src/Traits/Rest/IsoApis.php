<?php

namespace Xelon\VmWareClient\Traits\Rest;

use Xelon\VmWareClient\Requests\ApiRequest;

trait IsoApis
{
    use ApiRequest;

    public function mountImage(string $vmId, string $libraryItem)
    {
        if ($this->version >= 7) {
            $url = '/api/vcenter/iso/image?action=mount';
            $params = ['library_item' => $libraryItem, 'vm' => $vmId];
        } else {
            $url = "/rest/com/vmware/vcenter/iso/image/id:$libraryItem?~action=mount";
            $params = ['vm' => $vmId];
        }

        return $this->request('post', $url, ['form_params' => $params]);
    }

    public function unmountImage(string $vmId, string $cdRom)
    {
        if ($this->version >= 7) {
            $url = '/api/vcenter/iso/image?action=unmount';
            $params = ['cdrom' => $cdRom, 'vm' => $vmId];
        } else {
            $url = "/rest/com/vmware/vcenter/iso/image/id:$vmId?~action=unmount";
            $params = ['cdrom' => $cdRom];
        }

        return $this->request('post', $url, ['form_params' => $params]);
    }
}

<?php

namespace Xelon\VmWareClient\Traits\Rest;

use Xelon\VmWareClient\Requests\ApiRequest;

trait CisApis
{
    use ApiRequest;

    public function listAttachedTagsTagAssociation(string $vmId)
    {
        return $this->request(
            'post',
            '/api/cis/tagging/tag-association?action=list-attached-tags',
            [
                'json' => [
                    'object_id' => [
                        'type' => 'VirtualMachine',
                        'id' => $vmId,
                    ],
                ],
            ]
        );
    }

    public function atachTagAssociation(string $vmId, string $tagId)
    {
        return $this->request(
            'post',
            "/api/cis/tagging/tag-association/$tagId?action=attach",
            [
                'json' => [
                    'object_id' => [
                        'type' => 'VirtualMachine',
                        'id' => $vmId,
                    ],
                ],
            ]
        );
    }

    public function detachTagAssociation(string $vmId, string $tagId)
    {
        return $this->request(
            'post',
            "/api/cis/tagging/tag-association/$tagId?action=detach",
            [
                'json' => [
                    'object_id' => [
                        'type' => 'VirtualMachine',
                        'id' => $vmId,
                    ],
                ],
            ]
        );
    }
}

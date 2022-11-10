<?php

namespace Xelon\VmWareClient\Traits\Rest;

use Xelon\VmWareClient\Requests\ApiRequest;

trait OfvApis
{
    use ApiRequest;

    public function deployLibraryItem(string $ovfLibraryItemId, array $data)
    {
        $networkMappings = [
            'value' => $data['network_port_group'],
            'key' => 'VM Network',
        ];

        if ($this->version < 7) {
            $networkMappings = [$networkMappings];
        }

        $body = [
            'deployment_spec' => [
                'name' => $data['name'],
                'accept_all_EULA' => false,
                'default_datastore_id' => $data['default_datastore_id'],
                'storage_provisioning' => 'thin',
                'additional_parameters' => [
                    [
                        '@class' => 'com.vmware.vcenter.ovf.property_params',
                        'type' => 'PropertyParams',
                        'properties' => [
                            [
                                'instance_id' => '',
                                'class_id' => '',
                                'description' => 'In order to fit into a xml attribute, this value is base64 encoded . It will be decoded, and then processed normally as user-data.',
                                'id' => 'user-data',
                                'label' => 'Encoded user-data',
                                'category' => '',
                                'type' => 'string',
                                'value' => $data['user_data'],
                                'ui_optional' => false,
                            ],
                        ],
                    ],
                ],
                'network_mappings' => $networkMappings,
            ],
            'target' => [
                'resource_pool_id' => $data['resource_pool_id'],
            ],
        ];

        if (isset($data['folder_id'])) {
            $body['target']['folder_id'] = $data['folder_id'];
        }

        return $this->request(
            'post',
            $this->version >= 7
                ? "/api/vcenter/ovf/library-item/$ovfLibraryItemId?action=deploy"
                : "/rest/com/vmware/vcenter/ovf/library-item/id:$ovfLibraryItemId?~action=deploy",
            ['json' => $body]
        );
    }
}

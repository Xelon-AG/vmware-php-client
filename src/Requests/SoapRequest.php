<?php

namespace Xelon\VmWareClient\Requests;

trait SoapRequest
{
    private function vmRequest(string $method, string $vmId, array $requestBody = [])
    {
        $soapMessage = [
            '_this' => [
                '_' => $vmId,
                'type' => 'VirtualMachine',
            ],
        ];
        $soapMessage = array_merge($soapMessage, $requestBody);

        return $this->soapClient->$method($soapMessage);
    }
}

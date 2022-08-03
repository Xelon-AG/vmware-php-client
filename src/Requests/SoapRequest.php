<?php

namespace Xelon\VmWareClient\Requests;

trait SoapRequest
{
    private function vmRequest(string $method, string $vmId, array $requestBody = [])
    {
        $soapMessage = [
            '_this' =>[
              '_' => $vmId,
              'type' => 'VirtualMachine'
            ]
        ];
        $soapMessage = array_merge($soapMessage, $requestBody);
        return $this->soapClient->$method($soapMessage);
    }

    private function array_to_object($array) {
        $obj = new \stdClass();

        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = $this->array_to_object($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
        }

        return $obj;
    }
}

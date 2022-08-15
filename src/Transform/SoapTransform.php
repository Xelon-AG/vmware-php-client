<?php

namespace Xelon\VmWareClient\Transform;

use SoapVar;

trait SoapTransform
{
    public function arrayToSoapVar(array $array): array
    {
        $typeName = null;
        $data = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists('@type', $value)) {
                    $typeName = $value['@type'];
                    unset($value['@type']);
                }
                $data[$key] = new SoapVar($this->arrayToSoapVar($value), SOAP_ENC_OBJECT, $typeName);
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}

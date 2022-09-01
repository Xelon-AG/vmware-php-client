<?php

namespace Xelon\VmWareClient\Transform;

use SoapVar;
use stdClass;

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

                if (array_key_exists('type', $value)) {
                    $data[$key] = new SoapVar($value['_'], null, $value['type'], '', '', '');
                    continue;
                }

                // TODO: Get rid of BOGUS and ENV:Struct tags
                /*if (array_key_exists(0, $value)) {
                    $arrayData = [];

                    foreach ($value as $item) {
                        $arrayData[] = new SoapVar($this->arrayToSoapVar($item), SOAP_ENC_OBJECT, $typeName, 'VirtualDeviceConfigSpec', 'deviceChange', '');
                    }

                    $data[$key] = new SoapVar($arrayData, SOAP_ENC_OBJECT, '', '', 'deviceChanges', '');

                    continue;
                }*/

                if (array_key_exists(0, $value)) {
                    $arrayData = [];

                    foreach ($value as $item) {
                        $arrayData[] = new SoapVar($this->arrayToSoapVar($item), SOAP_ENC_OBJECT, $typeName, null, 'deviceChange', null);
                    }

                    $data[$key] = new SoapVar($arrayData, SOAP_ENC_OBJECT, null, 'deviceChange', null, null);

                    continue;
                }



                $data[$key] = new SoapVar($this->arrayToSoapVar($value), SOAP_ENC_OBJECT, $typeName, null, 'empty');
                $typeName = null;
            } elseif (!is_null($value)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function transformPropSet(array $data): stdClass
    {
        $newData = new stdClass();

        foreach ($data as $item) {
            $newData->{$item->name} = $item->val;
        }

        return $newData;
    }
}

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
                    $data[$key] = new SoapVar($value['_'], null, $value['type'], '', $key, '');

                    continue;
                }

                if (is_array($value) && array_key_exists(0, $value)) {
                    foreach ($value as $childItem) {

                        if (array_key_exists('@type', $childItem)) {
                            $typeName = $childItem['@type'];
                            unset($childItem['@type']);
                        }

                        if (array_key_exists('type', $childItem)) {
                            $data[] = new SoapVar($childItem['_'], null, $childItem['type'], '', $key, '');

                            continue;
                        }

                        $data[] = new SoapVar($this->arrayToSoapVar($childItem), SOAP_ENC_OBJECT, $typeName, null);
                    }

                    unset($array[$key]);

                    $deepArraySet = true;
                }

                if (!isset($deepArraySet)) {
                    $data[$key] = new SoapVar($this->arrayToSoapVar($value), SOAP_ENC_OBJECT, $typeName, null, $key);
                }

                $typeName = null;
            } elseif (! is_null($value)) {
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

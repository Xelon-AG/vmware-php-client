<?php

namespace Xelon\VmWareClient\Transform;

use SoapVar;
use stdClass;
use Xelon\VmWareClient\Types\Core\DynamicData;

trait SoapTransform
{
    private $soapTypes = [
        'string' => XSD_STRING,
        'integer' => XSD_INT,
        'boolean' => XSD_BOOLEAN,
        'double' => XSD_FLOAT,
        'array' => SOAP_ENC_OBJECT,
        'object' => SOAP_ENC_OBJECT,
    ];

    public function arrayToSoapVar(array $array): array
    {
        $typeName = null;
        $data = [];
        foreach ($array as $key => $value) {
            if (str_contains($key, ':')) {
                $keyString = explode(':', $key);
                $key = $keyString[0];
                $type = $keyString[1];
            }
            if (is_array($value) || $value instanceof DynamicData) {
                if ($value instanceof DynamicData) {
                    $typeName = (new \ReflectionClass($value))->getShortName();
                    $value = $value->toArray();
                }

                if (array_key_exists('@type', $value)) {
                    $typeName = $value['@type'];
                    unset($value['@type']);
                }

                if (array_key_exists('_', $value) && array_key_exists('type', $value)) {
                    $data[$key] = new SoapVar($value['_'], null, $value['type'], '', $key, '');

                    continue;
                }

                if (is_array($value) && array_key_exists(0, $value)) {
                    foreach ($value as $childItem) {
                        if ($childItem instanceof DynamicData) {
                            $typeName = (new \ReflectionClass($childItem))->getShortName();
                            $childItem = $childItem->toArray();
                        }

                        if (is_array($childItem)) {
                            if (array_key_exists('@type', $childItem)) {
                                $typeName = $childItem['@type'];
                                unset($childItem['@type']);
                            }

                            if (array_key_exists('_', $childItem) && array_key_exists('type', $childItem) && array_key_exists(0, $array[$key])) {
                                $data[] = new SoapVar($childItem['_'], XSD_STRING, $childItem['type'], null, $key);

                                continue;
                            } elseif (array_key_exists('_', $childItem) && array_key_exists('type', $childItem)) {
                                $data[$key] = new SoapVar($childItem['_'], null, $childItem['type'], '', $key, '');

                                continue;
                            }
                        } else {
                            $data[] = new SoapVar($childItem, null, null, null, $key);

                            continue;
                        }

                        $data[] = new SoapVar($this->arrayToSoapVar($childItem), SOAP_ENC_OBJECT, $typeName, null, $key);
                    }

                    $deepArraySet = true;
                }

                if (! isset($deepArraySet)) {
                    $data[$key] = new SoapVar($this->arrayToSoapVar($value), SOAP_ENC_OBJECT, $typeName, null, $key);
                }

                $typeName = null;
            } elseif (! is_null($value)) {
                $encoding = isset($type) ? XSD_STRING : null;
                $typeName = isset($type) ? 'xsd:string' : null;
                $data[$key] = new SoapVar($value, $encoding, $typeName, null, $key);
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

        return $this->transformToArrayValues($newData);
    }

    public function transformPropSetArray(array $data): array
    {
        $newData = [];

        foreach ($data as $item) {
            $newData[] = $this->transformPropSet(is_array($item->propSet) ? $item->propSet : [$item->propSet]);
        }

        return $newData;
    }

    /**
     * @return stdClass
     * This function transform to array objects that should be array type
     */
    public function transformToArrayValues(stdClass $object, int $startIndex = 0, ?int $onlyIndexPath = null)
    {
        $pathes = [
            ['latestPage', 'TaskInfo'],
            ['returnval', 'sampleInfo'],
            ['returnval', 'value', 'value'],
            ['returnval', 'config', 'consumerId'],
            ['layoutEx', 'disk'],
            ['layoutEx', 'disk', 'chain'],
            ['layoutEx', 'disk', 'chain', 'fileKey'],
            ['layoutEx', 'file'],
            ['layoutEx', 'snapshot'],
            ['layoutEx', 'snapshot', 'disk'],
            ['layoutEx', 'snapshot', 'disk', 'chain'],
            ['layoutEx', 'snapshot', 'disk', 'chain', 'fileKey'],
            ['snapshot', 'rootSnapshotList'],
            ['snapshot', 'rootSnapshotList', 'childSnapshotList'],
        ];

        $recursiveOblectNames = ['childSnapshotList'];

        foreach ($pathes as $indexPath => $path) {
            if ($onlyIndexPath && $onlyIndexPath !== $indexPath) {
                continue;
            }

            $lastIndex = count($path) - 1;
            $newObj = $object;

            foreach ($path as $index => $property) {
                if ($index < $startIndex || ! is_object($newObj) || empty((array) $newObj) || ! property_exists($newObj, $property)) {
                    continue;
                }

                $newObj = $newObj->{$property};

                $varName = "el_$indexPath";

                isset($$varName) ? $$varName = &$$varName->{$property} : $$varName = &$object->{$property};

                if ($index === $lastIndex && ! is_array($newObj)) {
                    $$varName = [$$varName];
                }

                if ($index !== $lastIndex && is_array($$varName)) {
                    foreach ($$varName as &$oblectItem) {
                        $oblectItem = $this->transformToArrayValues($oblectItem, $index + 1, $indexPath);
                    }
                }

                foreach ($recursiveOblectNames as $recursiveOblectName) {
                    if ($recursiveOblectName === $property) {
                        $this->transformToArrayValuesRecursive($$varName, $property);
                    }
                }
            }
        }

        return $object;
    }

    public function getListFilterQuery(array $filter): string
    {
        if ($this->version < 7) {
            foreach ($filter as $key => $value) {
                $filter["filter.$key"] = $value;
                unset($filter[$key]);
            }
        }

        return preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($filter, null, '&'));
    }

    private function transformToArrayValuesRecursive(&$object, string $propertyName)
    {
        if (is_array($object)) {
            foreach ($object as &$nestedObject) {
                $nestedObject = $this->transformToArrayValuesRecursive($nestedObject, $propertyName);
            }
        } else {
            if (isset($object->{$propertyName})) {
                if (is_array($object->{$propertyName})) {
                    foreach ($object->{$propertyName} as &$nestedObject1) {
                        $nestedObject1 = $this->transformToArrayValuesRecursive($nestedObject1, $propertyName);
                    }
                } else {
                    $object->{$propertyName} = [$this->transformToArrayValuesRecursive($object->{$propertyName}, $propertyName)];
                }
            }
        }

        return $object;
    }
}

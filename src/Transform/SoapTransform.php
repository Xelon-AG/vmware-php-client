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

                if (! isset($deepArraySet)) {
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

        return $this->transformToArrayValues($newData);
    }

    /**
     * @param stdClass $object
     * @return stdClass
     * This function transform to array objects that should be array type
     */
    public function transformToArrayValues(stdClass $object, int $startIndex = 0, ?int $onlyIndexPath = null)
    {
        $pathes = [
            ['latestPage', 'TaskInfo'],
            ['returnval', 'sampleInfo'],
            ['returnval', 'value', 'value'],
            ['layoutEx', 'file'],
            ['layoutEx', 'snapshot'],
            ['layoutEx', 'snapshot', 'disk'],
            ['layoutEx', 'snapshot', 'disk', 'chain'],
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
                if ($index < $startIndex || !is_object($newObj) || empty((array)$newObj) || !property_exists($newObj, $property)) {
                    continue;
                }

                $newObj = $newObj->{$property};

                $varName = "el_$indexPath";

                isset($$varName) ? $$varName = &$$varName->{$property} : $$varName = &$object->{$property};

                if ($index === $lastIndex && !is_array($newObj)) {
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

    private function transformToArrayValuesRecursive(&$object, string $propertyName)
    {
        if (is_array($object)) {
            foreach ($object as &$nestedObject) {
                $nestedObject = $this->transformToArrayValuesRecursive($nestedObject, $propertyName);
            }
        } else {
            if (isset($object->{$propertyName})) {
                $object->{$propertyName} = [$object->{$propertyName}];
                foreach ($object->{$propertyName} as &$nestedObject1) {
                    $nestedObject1 = $this->transformToArrayValuesRecursive($nestedObject1, $propertyName);
                }
            }
        }

        return $object;
    }
}

<?php

namespace Xelon\VmWareClient\Types\Core;

class DynamicData
{
    public function __construct(array $data = [])
    {
        foreach ($data as $property => $value) {
            if (property_exists(static::class, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    public function toArray(): array
    {
        $data = [];

        foreach ($this as $property => $value) {
            if ($value !== null) {
                $data[$property] = $value;
            }
        }

        return $data;
    }
}

<?php

namespace Xelon\VmWareClient\Types\Core;

class DynamicData implements \Countable, \JsonSerializable
{
    public function __construct(array $data = [])
    {
        foreach ($data as $property => $value) {
            if (property_exists(static::class, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * @return array
     * Generate array of class properties in order from parent to child
     */
    public function toArray(): array
    {
        $data = [];
        $classes = [];
        $properties = [];

        $currentClass = static::class;
        while ($currentClass && $currentClass !== self::class) {
            $classes[] = $currentClass;
            $currentClass = get_parent_class($currentClass);
        }

        $classes = array_reverse($classes);

        foreach ($classes as $class) {
            $classInfo = new \ReflectionClass($class);

            foreach ($classInfo->getProperties() as $prop) {
                if ($prop->class === $class) {
                    $properties[] = $prop->getName();
                }
            }
        }

        foreach ($properties as $property) {
            if ($this->$property !== null) {
                $data[$property] = $this->$property;
            }
        }

        return $data;
    }

    public function count(): int
    {
        $count = 0;

        foreach ($this as $property) {
            if ($property !== null) {
                $count++;
            }
        }

        return $count;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

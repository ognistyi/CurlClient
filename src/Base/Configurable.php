<?php

namespace Ognistyi\CurlClient\Base;


class Configurable
{
    public function __construct($config = [])
    {
        if (!empty($config)) {
            self::configure($this, $config);
        }

        $this->init();
    }

    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }
    
    public function init() {}
}
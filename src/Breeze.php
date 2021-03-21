<?php
namespace Haxibiao\Breeze;

class Breeze
{
    public static $assets = [];

    public static function allAssets()
    {
        return static::$assets;
    }

    public static function asset($name, $path)
    {
        static::$assets[$name] = $path;
        return new static;
    }
}

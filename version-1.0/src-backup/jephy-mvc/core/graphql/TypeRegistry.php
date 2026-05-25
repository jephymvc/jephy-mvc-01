<?php
namespace App\Core\GraphQL;

class TypeRegistry
{
    private static $types = [];
    private static $instances = [];

    public static function get($name)
    {
        if (!isset(self::$types[$name])) {
            throw new \Exception("Type {$name} not found in registry");
        }

        if (!isset(self::$instances[$name])) {
            $typeClass = self::$types[$name];
            $instance = new $typeClass();
            self::$instances[$name] = $instance->getType();
        }

        return self::$instances[$name];
    }

    public static function register($name, $class)
    {
        self::$types[$name] = $class;
    }
}
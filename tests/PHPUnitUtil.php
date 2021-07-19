<?php

namespace Tests;

use Exception;
use ReflectionClass;
use ReflectionMethod;

class PHPUnitUtil
{
    public static function callMethod(string $className, string $name, array $args = [])
    {
        try {
            $class = new ReflectionClass($className);
            $method = $class->getMethod($name);
            $method->setAccessible(true);

            return self::invoke($class, $method, $args);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    private static function invoke(ReflectionClass $class, ReflectionMethod $method, array $args)
    {
        return $method->isStatic() ?
            $method->invokeArgs(null, $args) :
            $method->invokeArgs($class->newInstance(), $args);
    }
}

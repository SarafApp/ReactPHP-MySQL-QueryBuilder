<?php

namespace Saraf\QB\QueryBuilder\Abstracts;

abstract class Facade
{
    protected static function getFacadeAccessor()
    {
        throw new \RuntimeException("Facade access not implemented");
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $accessor = static::getFacadeAccessor();

        return $accessor->$name(...$arguments);
    }
}
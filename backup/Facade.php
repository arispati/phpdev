<?php

namespace Phpdev;

use Illuminate\Container\Container;

class Facade
{
    /**
     * The key for the binding in the container.
     */
    public static function containerKey(): string
    {
        return 'PhpDevBackup\\App\\' . basename(str_replace('\\', '/', get_called_class()));
    }

    /**
     * Call a non-static method on the facade.
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        $resolvedInstance = Container::getInstance()->make(static::containerKey());

        return call_user_func_array([$resolvedInstance, $method], $parameters);
    }
}

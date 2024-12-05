<?php

namespace PhpDev\Facade;

use PhpDev\Helper\Facade;

/**
 * Configuration facade
 *
 * @method static array read(?string $key = null)
 * @method static void addPhp(string $php)
 * @method static array synchPhp()
 * @method static void addSite(string $type, string $site, string $path, ?string $php = null)
 * @method static void removeSite(string $site)
 *
 * @method static array updateKey(string $key, mixed $value)
 *
 * @see \PhpDev\App\Config
 */
class Config extends Facade
{
    //
}

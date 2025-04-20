<?php

namespace PhpDev\Facade;

use PhpDev\Helper\Facade;

/**
 * Configuration facade
 *
 * @method static void init()
 * @method static array read(?string $key = null)
 * @method static bool siteExists(string $site)
 * @method static array|null siteGet($site)
 * @method static bool phpExists($php)
 * @method static array synchPhp()
 * @method static void addSite(string $type, string $site, string $path, ?string $php = null, ?string $ssl = null)
 * @method static void removeSite(string $site)
 *
 * @see \PhpDev\App\Config
 */
class Config extends Facade
{
    //
}

<?php

namespace PhpDev\Facade;

use PhpDev\Helper\Facade;

/**
 * Nginx facade
 *
 * @method static void install()
 * @method static void start()
 * @method static void stop()
 * @method static void restart()
 * @method static void createConfiguration(string $site, string $path, string $php)
 * @method static void createProxyConfiguration(string $site, string $destination)
 * @method static void removeConfiguration(string $site)
 *
 * @see \PhpDev\App\Nginx
 */
class Nginx extends Facade
{
    //
}

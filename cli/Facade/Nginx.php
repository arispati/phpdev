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
 * @method static void createConfiguration(string $site, string $path, string $php, ?string $ssl = null)
 * @method static void createProxyConfiguration(string $site, string $destination, ?string $ssl = null)
 * @method static void removeConfiguration(string $site)
 * @method static void backupConfiguration(string $site)
 * @method static void removeBackupConfiguration(string $site)
 * @method static void restoreBackupConfiguration(string $site)
 * @method static boolean isBackupExists(string $site)
 *
 * @see \PhpDev\App\Nginx
 */
class Nginx extends Facade
{
    //
}

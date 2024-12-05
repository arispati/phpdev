<?php

namespace PhpDev\Facade;

use PhpDev\Helper\Facade;

/**
 * PHP FPM facade
 *
 * @method static void install()
 * @method static string getVersion(?string $php = null)
 * @method static bool installed(string $version)
 * @method static void createConfigurationFiles(string $phpVersion)
 * @method static void start(?string $phpVersion = null)
 * @method static void stop(string|array $phpVersion)
 * @method static void restart(string|array|null $phpVersion = null)
 *
 * @see \PhpDev\App\PhpFpm
 */
class PhpFpm extends Facade
{
    //
}

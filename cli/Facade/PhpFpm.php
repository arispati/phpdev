<?php

namespace PhpDev\Facade;

use PhpDev\Helper\Facade;

/**
 * PHP FPM facade
 *
 * @method static string getVersion(?string $php = null)
 * @method static bool installed(string $version)
 * @method static void createConfigurationFiles(string $phpVersion)
 * @method static void start(?string $phpVersion = null)
 *
 * @see \PhpDev\App\PhpFpm
 */
class PhpFpm extends Facade
{
    //
}

<?php

namespace PhpDev\Facade;

use PhpDev\Helper\Facade;

/**
 * PHP FPM facade
 *
 * @method static string getVersion(?string $php = null)
 * @method static bool installed(string $version)
 * @method static void createConfigurationFiles(string $phpVersion)
 *
 * @see \PhpDev\App\PhpFpm
 */
class PhpFpm extends Facade
{
    //
}

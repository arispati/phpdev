<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Config;
use PhpDev\Facade\Nginx;
use PhpDev\Facade\PhpFpm;
use PhpDev\Helper\Helper;

class InstallCommand
{
    /**
     * Command description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Install PhpDev services';
    }

    public function __invoke()
    {
        Helper::info(PHP_EOL . 'Installing PhpDev services');
        Helper::write();
        // init config
        Config::init();
        // install nginx configuration
        Nginx::install();
        Helper::write();
        // install php fpm configuration
        PhpFpm::install();

        Helper::info(PHP_EOL . 'PhpDev installed successfully!');
    }
}

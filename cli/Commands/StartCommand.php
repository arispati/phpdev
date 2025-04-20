<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Nginx;
use PhpDev\Facade\PhpFpm;
use PhpDev\Helper\Helper;

class StartCommand
{
    /**
     * Command description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Start PhpDev services';
    }

    public function __invoke()
    {
        Helper::info(PHP_EOL . 'Starting PhpDev services');
        Helper::write();
        // start nginx
        Nginx::start();
        Helper::write();
        // start php fpm
        PhpFpm::start();

        Helper::info(PHP_EOL . 'All PhpDev services have been started.');
    }
}

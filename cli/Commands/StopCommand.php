<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Nginx;
use PhpDev\Facade\PhpFpm;
use PhpDev\Helper\Helper;

class StopCommand
{
    /**
     * Command description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Stop PhpDev services';
    }

    public function __invoke()
    {
        Helper::info(PHP_EOL . 'Stopping PhpDev services');
        Helper::write();
        // stop nginx
        Nginx::stop();
        Helper::write();
        // stop php fpm
        PhpFpm::stop();

        Helper::info(PHP_EOL . 'PhpDev services have been stopped.');
    }
}

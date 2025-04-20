<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Nginx;
use PhpDev\Facade\PhpFpm;
use PhpDev\Helper\Helper;

class RestartCommand
{
    /**
     * Command description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Restart PhpDev services';
    }

    public function __invoke()
    {
        Helper::info(PHP_EOL . 'Restart PhpDev service');
        Helper::write();
        // restart nginx
        Nginx::restart();
        Helper::write();
        // restart PHP FPM
        PhpFpm::restart();

        Helper::info(PHP_EOL . 'PhpDev services has been restarted');
    }
}

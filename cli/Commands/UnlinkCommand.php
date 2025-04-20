<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Config;
use PhpDev\Facade\Nginx;
use PhpDev\Facade\PhpFpm;
use PhpDev\Helper\Helper;
use Silly\Command\Command;

class UnlinkCommand
{
    /**
     * Command description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Unlink site';
    }

    /**
     * Command argument & option description
     *
     * @return array
     */
    public static function descriptionArgOpt(): array
    {
        return [
            'site' => 'Site name'
        ];
    }

    public function __invoke($site)
    {
        // validate site
        if (! Config::siteExists($site)) {
            Helper::warning(PHP_EOL . 'Error:');
            Helper::write("Site name {$site} is not linked yet");
            return Command::FAILURE;
        }
        Helper::info(sprintf('Unlinking %s', $site));
        Helper::write();
        // remove site config
        Nginx::removeConfiguration($site);
        // restart nginx
        Nginx::restart();
        // remove site config
        Config::removeSite($site);
        // synch PHP FPM
        if ($unusedPhp = Config::synchPhp()) {
            Helper::info(PHP_EOL . 'There is an unused PHP FPM, stop it service.');
            PhpFpm::stop($unusedPhp);
        }

        Helper::info(PHP_EOL . sprintf('%s successfully unlinked', $site));
    }
}

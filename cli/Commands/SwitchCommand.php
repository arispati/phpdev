<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Config;
use PhpDev\Facade\Nginx;
use PhpDev\Facade\PhpFpm;
use PhpDev\Helper\Helper;
use Silly\Command\Command;

class SwitchCommand
{
    /**
     * Command description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Switch PHP version for the site';
    }

    /**
     * Command argument & option description
     *
     * @return array
     */
    public static function descriptionArgOpt(): array
    {
        return [
            'site' => 'Site name',
            'php' => 'PHP version'
        ];
    }

    public function __invoke($site, $php)
    {
        // ensure site exists
        if (! Config::siteExists($site)) {
            Helper::warning(PHP_EOL . 'Error:');
            Helper::write('Site name is not linked yet');
            // exit command
            return Command::FAILURE;
        }
        $siteConfig = Config::siteGet($site);
        // only 'link' type can be switch
        if ($siteConfig['type'] == 'proxy') {
            Helper::warning(PHP_EOL . 'Error:');
            Helper::write('Switch command not compatible with proxy type');
        }
        // PHP FPM
        $php = PhpFpm::getVersion($php);
        // validate php version installed
        if (! PhpFpm::installed($php)) {
            Helper::warning(PHP_EOL . 'Error:');
            Helper::write(sprintf('PHP %s not installed yet', $php));
            // exit command
            return Command::FAILURE;
        }
        Helper::info(sprintf(PHP_EOL . 'Switching %s from PHP %s to PHP %s', $site, $siteConfig['php'], $php));
        Helper::write();
        // remove site config
        Nginx::removeConfiguration($site);
        // create nginx configuration
        Nginx::createConfiguration($site, $siteConfig['path'], $php, $siteConfig['ssl_path']);
        // restart nginx
        Nginx::restart();
        // validate php configuration
        if (! Config::phpExists($php)) {
            Helper::write();
            PhpFpm::createConfigurationFiles($php);
            // start php fpm
            PhpFpm::start($php);
        }
        // update site to config
        Config::addSite('link', $site, $siteConfig['path'], $php);
        // synch PHP FPM
        if ($unused = Config::synchPhp()) {
            Helper::info(PHP_EOL . 'There is an unused PHP FPM');
            // new line
            PhpFpm::stop($unused);
        }

        Helper::info(PHP_EOL . sprintf('%s has been switched to PHP %s', $site, $php));
    }
}

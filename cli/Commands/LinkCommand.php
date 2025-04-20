<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Config;
use PhpDev\Facade\Nginx;
use PhpDev\Facade\PhpFpm;
use PhpDev\Facade\Site;
use PhpDev\Helper\Helper;
use Silly\Command\Command;

class LinkCommand
{
    /**
     * Command description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Link the current working directory';
    }

    /**
     * Command argument & option description
     *
     * @return array
     */
    public static function descriptionArgOpt(): array
    {
        return [
            'path' => 'Root directory path for the site. Default: current directory path',
            '--site' => 'Site name. Default: current directory name',
            '--php' => 'Which php version to use. Default: current php version',
            '--tld' => 'Top Level Domain. Default: .test',
            '--ssl' => 'SSL certificate folder path'
        ];
    }

    public function __invoke($path, $site, $php, $tld, $ssl)
    {
        // define variable
        $path = Site::path($path);
        $site = Site::name($site, $tld);
        // validate site
        if (Config::siteExists($site)) {
            Helper::warning(PHP_EOL . 'Error:');
            Helper::write('Site name already linked');
            // exit command
            return Command::FAILURE;
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
        Helper::info(sprintf(PHP_EOL . 'Linking %s with PHP %s', $site, $php));
        Helper::write();
        // create nginx configuration
        Nginx::createConfiguration($site, $path, $php, $ssl);
        // restart nginx
        Nginx::restart();
        // validate php configuration
        if (! Config::phpExists($php)) {
            Helper::write();
            PhpFpm::createConfigurationFiles($php);
            // start php fpm
            PhpFpm::start($php);
        }
        // add site to config
        Config::addSite('link', $site, $path, $php, $ssl);

        Helper::info(PHP_EOL . sprintf('%s has been linked', $site));
    }
}

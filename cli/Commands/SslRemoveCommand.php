<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Config;
use PhpDev\Facade\Nginx;
use PhpDev\Helper\Helper;
use Silly\Command\Command;

class SslRemoveCommand
{
    /**
     * Command description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Add SSL Certificate to the site';
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
        // ensure site exists
        if (! Config::siteExists($site)) {
            Helper::warning(PHP_EOL . 'Error:');
            Helper::write('Site name is not linked yet');
            // exit command
            return Command::FAILURE;
        }
        Helper::info(sprintf('Remove SSL on %s', $site));
        Helper::write();
        // get site config
        $siteConfig = Config::siteGet($site);
        // backup site config
        Nginx::backupConfiguration($site);
        // create nginx configuration
        if ($siteConfig['type'] == 'proxy') {
            // create nginx configuration
            Nginx::createProxyConfiguration($site, $siteConfig['path']);
        } else {
            Nginx::createConfiguration($site, $siteConfig['path'], $siteConfig['php']);
        }
        // remove backup
        Nginx::removeBackupConfiguration($site);
        // restart nginx
        Nginx::restart();
        // update site to config
        Config::addSite('link', $site, $siteConfig['path'], $siteConfig['php']);

        Helper::info(PHP_EOL . sprintf('SSL has been removed to the site: %s', $site));
    }
}

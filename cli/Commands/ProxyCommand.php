<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Config;
use PhpDev\Facade\Nginx;
use PhpDev\Facade\Site;
use PhpDev\Helper\Helper;
use Silly\Command\Command;

class ProxyCommand
{
    /**
     * Command description
     *
     * @return string
     */
    public static function description(): string
    {
        return 'Add proxy site';
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
            'destination' => 'Proxy destination',
            '--tld' => 'Top Level Domain. Default: .test',
            '--ssl' => 'SSL certificate folder path'
        ];
    }

    public function __invoke($site, $destination, $tld, $ssl)
    {
        $site = Site::name($site, $tld);
        // validate site
        if (Config::siteExists($site)) {
            Helper::warning(PHP_EOL . 'Error:');
            Helper::write('Site name already linked');
            // exit command
            return Command::FAILURE;
        }
        Helper::info(sprintf(PHP_EOL . 'Proxying %s to %s', $site, $destination));
        Helper::write();
        // create nginx configuration
        Nginx::createProxyConfiguration($site, $destination, $ssl);
        // restart nginx
        Nginx::restart();
        // add site to config
        Config::addSite('proxy', $site, $destination, ssl: $ssl);

        Helper::info(PHP_EOL . sprintf('%s has been proxied to %s', $site, $destination));
    }
}

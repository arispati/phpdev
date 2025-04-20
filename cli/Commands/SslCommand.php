<?php

namespace PhpDev\Commands;

use PhpDev\Facade\Config;
use PhpDev\Facade\Nginx;
use PhpDev\Helper\Helper;
use Silly\Command\Command;

class SslCommand
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
            'site' => 'Site name',
            'path' => 'Folder path to the SSL Certificate'
        ];
    }

    public function __invoke($site, $path)
    {
        // ensure site exists
        if (! Config::siteExists($site)) {
            Helper::warning(PHP_EOL . 'Error:');
            Helper::write('Site name is not linked yet');
            // exit command
            return Command::FAILURE;
        }
        Helper::info(sprintf('Add SSL to %s', $site));
        Helper::write();
        // validate cert file
        $certPath = realpath($path);
        // validate path
        if (! $certPath) {
            throw new \Exception('Invalid SSL certificate path');
        }
        $certs = ['ssl_certificate.crt', 'ssl_certificate.key'];
        foreach ($certs as $cert) {
            $currentPath = $certPath . '/' . $cert;
            if (! file_exists($currentPath)) {
                throw new \Exception("File not found: $currentPath");
            }
        }
        // get site config
        $siteConfig = Config::siteGet($site);
        // backup site config
        Nginx::backupConfiguration($site);
        // create nginx configuration
        if ($siteConfig['type'] == 'proxy') {
            // create nginx configuration
            Nginx::createProxyConfiguration($site, $siteConfig['path'], $path);
        } else {
            Nginx::createConfiguration($site, $siteConfig['path'], $siteConfig['php'], $path);
        }
        // remove backup
        Nginx::removeBackupConfiguration($site);
        // restart nginx
        Nginx::restart();
        // update site to config
        Config::addSite('link', $site, $siteConfig['path'], $siteConfig['php'], $path);

        Helper::info(PHP_EOL . sprintf('SSL has been added to the site: %s', $site));
    }
}

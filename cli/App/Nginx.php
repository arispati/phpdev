<?php

namespace PhpDev\App;

use PhpDev\Helper\Cli;
use PhpDev\Helper\File;
use PhpDev\Helper\Helper;

class Nginx
{
    /**
     * Class constructor
     *
     * @param PhpFpm $php
     */
    public function __construct(
        protected PhpFpm $php
    ) {
        //
    }

    /**
     * Restart Nginx service
     *
     * @return void
     */
    public function restart(): void
    {
        Helper::info('Restarting nginx service');
        // run command
        Cli::runCommand('sudo service nginx restart');
    }

    /**
     * Start the Nginx service
     *
     * @return void
     */
    public function start(): void
    {
        Helper::info('Starting nginx');
        // run command
        Cli::runCommand('sudo service nginx start');
    }

    /**
     * Stop the Nginx service
     *
     * @return void
     */
    public function stop(): void
    {
        Helper::info('Stopping nginx');
        // run command
        Cli::runCommand('sudo service nginx stop');
    }

    /**
     * Create configuration for site
     *
     * @param string $site
     * @param string $path
     * @param string $php
     * @return void
     */
    public function createConfiguration(string $site, string $path, string $php): void
    {
        Helper::info(sprintf('Installing %s configuration', $site));
        // site configuration path
        $siteConfigPath = sprintf('%s/%s', PHPDEV_NGINX_SITE_PATH, $site);
        // site configuration
        $siteConfig = str_replace(
            ['PHPDEV_SERVER_NAME', 'PHPDEV_SERVER_ROOT_DIR', 'PHPDEV_PHP_FPM'],
            [$site, $path, $this->php->fpmSockPath($php)],
            File::getStub('site.conf')
        );
        // create site configuration file
        Cli::runCommand(sprintf('sudo touch %s', $siteConfigPath));
        // write site configuration
        Cli::runCommand("echo '$siteConfig' | sudo tee $siteConfigPath");
    }

    /**
     * Remove site configuration
     *
     * @param string $site
     * @return void
     */
    public function removeConfiguration(string $site): void
    {
        Helper::info(sprintf('Removing %s configuration', $site));
        // site configuration path
        $siteConfigPath = sprintf('%s/%s', PHPDEV_NGINX_SITE_PATH, $site);
        // remove site configuration
        Cli::runCommand(sprintf('sudo rm %s', $siteConfigPath));
    }
}

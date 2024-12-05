<?php

namespace PhpDev\App;

use PhpDev\Helper\Cli;
use PhpDev\Helper\File;
use PhpDev\Helper\Helper;

class Nginx
{
    public const NGINX_DEFAULT_SITE = '/etc/nginx/sites-enabled/default';

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
     * Install and configure Nginx.
     *
     * @return void
     */
    public function install(): void
    {
        $this->installConfiguration();

        $this->restart();
    }

    /**
     * Create (or re-create) the Nginx configuration files
     *
     * @return void
     */
    public function installConfiguration(): void
    {
        Helper::info('Installing nginx configuration...');

        // backup original conf
        if (! file_exists(PHPDEV_NGINX_CONF_PATH . '-phpdev-backup')) {
            Cli::runCommand(sprintf(
                'sudo mv %s %s',
                PHPDEV_NGINX_CONF_PATH,
                PHPDEV_NGINX_CONF_PATH . '-phpdev-backup'
            ));
        }

        $contents = str_replace(
            'PHPDEV_USER',
            PHPDEV_USER,
            file::getStub('nginx.conf')
        );

        Cli::runCommand('sudo touch ' . PHPDEV_NGINX_CONF_PATH);

        Cli::runCommand(
            "echo '$contents' | sudo tee " . PHPDEV_NGINX_CONF_PATH
        );

        // configure default host
        $defaultConfig = str_replace(
            ['PHPDEV_SERVER_NAME', 'PHPDEV_SERVER_ROOT_DIR', 'PHPDEV_PHP_FPM'],
            ['localhost', '/var/www/html', $this->php->fpmSockPath($this->php->getVersion())],
            File::getStub('site.conf')
        );

        Cli::runCommand(sprintf(
            'sudo rm %s && touch %s',
            self::NGINX_DEFAULT_SITE,
            self::NGINX_DEFAULT_SITE
        ));

        Cli::runCommand(
            "echo '$defaultConfig' | sudo tee " . self::NGINX_DEFAULT_SITE
        );
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
     * Create proxy configuration for site
     *
     * @param string $site
     * @param string $destination
     * @return void
     */
    public function createProxyConfiguration(string $site, string $destination): void
    {
        Helper::info(sprintf('Installing %s configuration', $site));
        // site configuration path
        $siteConfigPath = sprintf('%s/%s', PHPDEV_NGINX_SITE_PATH, $site);
        // site configuration
        $siteConfig = str_replace(
            ['PHPDEV_SERVER_NAME', 'PHPDEV_PROXY_DESTINATION'],
            [$site, $destination],
            File::getStub('proxy.conf')
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

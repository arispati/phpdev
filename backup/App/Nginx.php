<?php

namespace PhpDevBackup\App;

use PhpDevBackup\Tools\CommandLine;
use PhpDevBackup\Tools\Filesystem;

use function PhpDevBackup\info;
use function PhpDevBackup\user;

class Nginx
{
    public const NGINX_CONFIG_PATH = '/etc/nginx/nginx.conf';
    public const NGINX_DEFAULT_SITE = '/etc/nginx/sites-enabled/default';
    public const NGINX_SITE_CONFIG = '/etc/nginx/sites-enabled';

    public function __construct(
        protected CommandLine $cli,
        protected Filesystem $file,
        protected PhpFpm $php
    ) {
        //
    }

    /**
     * Install and configure Nginx.
     */
    public function install()
    {
        $this->installConfiguration();
        $this->restart();
    }

    /**
     * Create (or re-create) the Nginx configuration files.
     */
    public function installConfiguration()
    {
        info('Installing nginx configuration...');

        // backup original conf
        if (! file_exists(self::NGINX_CONFIG_PATH . '-phpdev-backup')) {
            $this->cli->runCommand(sprintf(
                'sudo mv %s %s',
                self::NGINX_CONFIG_PATH,
                self::NGINX_CONFIG_PATH . '-phpdev-backup'
            ));
        }

        $contents = str_replace(
            'PHPDEV_USER',
            user(),
            $this->file->getStub('nginx.conf')
        );

        $this->cli->runCommand('sudo touch ' . self::NGINX_CONFIG_PATH);

        $this->cli->runCommand(
            "echo '$contents' | sudo tee " . self::NGINX_CONFIG_PATH
        );

        // configure default host
        $defaultConfig = str_replace(
            ['PHPDEV_SERVER_NAME', 'PHPDEV_SERVER_ROOT_DIR', 'PHPDEV_PHP_FPM'],
            ['localhost', '/var/www/html', $this->php->fpmSockPath()],
            $this->file->getStub('site.conf')
        );

        $this->cli->runCommand(sprintf(
            'sudo rm %s && touch %s',
            self::NGINX_DEFAULT_SITE,
            self::NGINX_DEFAULT_SITE
        ));

        $this->cli->runCommand(
            "echo '$defaultConfig' | sudo tee " . self::NGINX_DEFAULT_SITE
        );
    }

    /**
     * Restart Nginx service
     */
    public function restart()
    {
        info('Restarting nginx service...');

        $this->cli->runCommand('sudo service nginx restart');
    }

    /**
     * Start the Nginx service
     */
    public function start(): void
    {
        info('Starting nginx...');

        $this->cli->runCommand('sudo service nginx start');
    }

    /**
     * Stop the Nginx service
     */
    public function stop(): void
    {
        info('Stopping nginx...');

        $this->cli->runCommand('sudo service nginx stop');
    }

    public function createConfiguration(string $site, string $path, string $php)
    {
        info(sprintf('Installing %s configuration', $site));
        // site configuration path
        $siteConfigPath = sprintf('%s/%s', self::NGINX_SITE_CONFIG, $site);
        // site configuration
        $siteConfig = str_replace(
            ['PHPDEV_SERVER_NAME', 'PHPDEV_SERVER_ROOT_DIR', 'PHPDEV_PHP_FPM'],
            [$site, $path, $this->php->fpmSockPath($php)],
            $this->file->getStub('site.conf')
        );
        // create site configuration file
        $this->cli->runCommand(sprintf('sudo touch %s', $siteConfigPath));
        // write site configuration
        $this->cli->runCommand("echo '$siteConfig' | sudo tee $siteConfigPath");
    }

    public function removeConfiguration(string $site)
    {
        info(sprintf('Removing %s configuration', $site));
        // site configuration path
        $siteConfigPath = sprintf('%s/%s', self::NGINX_SITE_CONFIG, $site);
        // remove site configuration
        $this->cli->runCommand(sprintf('sudo rm %s', $siteConfigPath));
    }
}
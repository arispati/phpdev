<?php

namespace PhpDev\App;

use PhpDev\Facade\Config;
use PhpDev\Facade\PhpFpm;
use PhpDev\Facade\Site;
use PhpDev\Helper\Cli;
use PhpDev\Helper\File;
use PhpDev\Helper\Helper;

class Nginx
{
    public const NGINX_DEFAULT_SITE = '/etc/nginx/sites-enabled/default';
    public const NGINX_DEFAULT_HTML = '/var/www/html/index.html';

    /**
     * Install and configure Nginx
     *
     * @return void
     */
    public function install(): void
    {
        // install configuration
        $this->installConfiguration();
        // renew site configuration
        Helper::write();
        $this->renewSiteConfig();
        // restart nginx service
        Helper::write();
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
        // apply nginx.conf
        $contents = str_replace('PHPDEV_USER', PHPDEV_USER, file::getStub('nginx.conf'));
        Cli::runCommand('sudo touch ' . PHPDEV_NGINX_CONF_PATH);
        Cli::runCommand(
            "echo '$contents' | sudo tee " . PHPDEV_NGINX_CONF_PATH
        );
        // configure default host
        $defaultConfig = str_replace(
            ['PHPDEV_HOME_PATH', 'PHPDEV_SERVER_NAME', 'PHPDEV_SERVER_ROOT_DIR', 'PHPDEV_PHP_FPM'],
            [PHPDEV_HOME_PATH, 'localhost', '/var/www/html', PhpFpm::fpmSockPath(PhpFpm::getVersion())],
            File::getStub('nginx-default.conf')
        );
        Cli::runCommand(sprintf(
            'sudo rm %s && touch %s',
            self::NGINX_DEFAULT_SITE,
            self::NGINX_DEFAULT_SITE
        ));
        Cli::runCommand(
            "echo '$defaultConfig' | sudo tee " . self::NGINX_DEFAULT_SITE
        );
        // default html template
        $defaultHtml = File::getStub('welcome.html');
        Cli::runCommand(sprintf(
            'sudo rm %s && touch %s',
            self::NGINX_DEFAULT_HTML,
            self::NGINX_DEFAULT_HTML
        ));
        Cli::runCommand(
            "echo '$defaultHtml' | sudo tee " . self::NGINX_DEFAULT_HTML
        );
    }

    /**
     * Renew site configurations
     *
     * @return void
     */
    public function renewSiteConfig(): void
    {
        // get site cofnig
        $configs = Config::read('sites');
        // validate confis
        if (! empty($configs)) {
            Helper::info('Renew site configurations');
            // iterate site configs
            foreach ($configs as $site => $config) {
                switch ($config['type']) {
                    case 'link':
                        $this->createConfiguration($site, $config['path'], $config['php'], $config['ssl_path'] ?? null);
                        break;
                    case 'proxy':
                        $this->createProxyConfiguration($site, $config['path'], $config['ssl_path'] ?? null);
                        break;
                }
            }
        }
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
     * Test config file
     *
     * @param string $site
     * @return void
     * @throws \Exception
     */
    public function testConf(string $site): void
    {
        // run command
        $test = Cli::runCommand('sudo nginx -t');
        // throw an error if failed
        if (! Helper::contains($test, 'test is successful')) {
            Helper::write('Nginx test configuration failed');
            // remove config file
            $this->removeConfiguration($site);
            // check backup
            if ($this->isBackupExists($site)) {
                // restore backup
                $this->restoreBackupConfiguration($site);
                // restart nginx service
                $this->restart();
            }
            // throw an error
            throw new \Exception($test);
        }
    }

    /**
     * Site config path
     *
     * @param string $site
     * @return string
     */
    public function configPath(string $site): string
    {
        return sprintf('%s/%s', PHPDEV_NGINX_SITE_PATH, $site);
    }

    /**
     * Create configuration for site
     *
     * @param string $site
     * @param string $path
     * @param string $php
     * @param string|null $ssl
     * @return void
     */
    public function createConfiguration(string $site, string $path, string $php, ?string $ssl = null): void
    {
        Helper::info(sprintf('Installing %s configuration', $site));
        // site configuration path
        $siteConfigPath = $this->configPath($site);
        // site configuration
        $stubFile = 'nginx-site.conf';
        $search = ['PHPDEV_HOME_PATH', 'PHPDEV_SERVER_NAME', 'PHPDEV_SERVER_ROOT_DIR', 'PHPDEV_PHP_FPM'];
        $replace = [PHPDEV_HOME_PATH, $site, $path, PhpFpm::fpmSockPath($php)];
        if (! is_null($ssl)) {
            $stubFile = 'nginx-site-ssl.conf';
            $certPath = realpath($ssl);
            // validate certificate path
            if (! $certPath) {
                throw new \Exception('Invalid SSL certificate path');
            }
            // add to replacer
            $search = array_merge($search, ['PHPDEV_SSL_PATH']);
            $replace = array_merge($replace, [$certPath]);
        }
        $siteConfig = str_replace($search, $replace, File::getStub($stubFile));
        // create site configuration file
        Cli::runCommand(sprintf('sudo touch %s', $siteConfigPath));
        // write site configuration
        Cli::runCommand("echo '$siteConfig' | sudo tee $siteConfigPath");

        // Create log directory and file
        $logPath = PHPDEV_HOME_PATH . "/log/nginx/{$site}";
        File::ensureDirExists($logPath);
        File::touch(sprintf('%s/%s-%s', $logPath, $site, 'access.log'));
        File::touch(sprintf('%s/%s-%s', $logPath, $site, 'error.log'));

        // test configuration file
        $this->testConf($site);
    }

    /**
     * Create proxy configuration for site
     *
     * @param string $site
     * @param string $destination
     * @param string|null $ssl
     * @return void
     */
    public function createProxyConfiguration(string $site, string $destination, ?string $ssl = null): void
    {
        Helper::info(sprintf('Installing %s configuration', $site));
        // site configuration path
        $siteConfigPath = $this->configPath($site);
        // site configuration
        $stubFile = 'nginx-proxy.conf';
        $search = ['PHPDEV_HOME_PATH', 'PHPDEV_SERVER_NAME', 'PHPDEV_PROXY_DESTINATION'];
        $replace = [PHPDEV_HOME_PATH, $site, $destination];
        if (! is_null($ssl)) {
            $stubFile = 'nginx-proxy-ssl.conf';
            $certPath = realpath($ssl);
            // validate certificate path
            if (! $certPath) {
                throw new \Exception('Invalid SSL certificate path');
            }
            // add to replacer
            $search = array_merge($search, ['PHPDEV_SSL_PATH']);
            $replace = array_merge($replace, [$certPath]);
        }
        // site configuration
        $siteConfig = str_replace($search, $replace, File::getStub($stubFile));
        // create site configuration file
        Cli::runCommand(sprintf('sudo touch %s', $siteConfigPath));
        // write site configuration
        Cli::runCommand("echo '$siteConfig' | sudo tee $siteConfigPath");

        // Create log directory and file
        // Create log directory and file
        $logPath = PHPDEV_HOME_PATH . "/log/nginx/{$site}";
        File::ensureDirExists($logPath);
        File::touch(sprintf('%s/%s-%s', $logPath, $site, 'access.log'));
        File::touch(sprintf('%s/%s-%s', $logPath, $site, 'error.log'));

        // test configuration file
        $this->testConf($site);
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
        $siteConfigPath = $this->configPath($site);
        // remove site configuration
        Cli::runCommand(sprintf('sudo rm %s', $siteConfigPath));
        // remove log file
        $logDirPath = sprintf('%s/log/nginx/%s', PHPDEV_HOME_PATH, $site);
        // ensure directory exists
        if (File::isDir($logDirPath)) {
            File::deleteDirectory($logDirPath);
        }
    }

    /**
     * Backup configuration
     *
     * @param string $site
     * @return void
     */
    public function backupConfiguration(string $site): void
    {
        Helper::info(sprintf('Backup %s configuration', $site));
        // site configuration path
        $configPath = $this->configPath($site);
        // backup site configuration
        Cli::runCommand(sprintf('sudo mv %s %s-phpdev-backup', $configPath, $configPath));
    }

    /**
     * Remove backup confgiuration
     *
     * @param string $site
     * @return void
     */
    public function removeBackupConfiguration(string $site): void
    {
        Helper::info(sprintf('Removing %s backup configuration', $site));
        // site configuration path
        $backupPath = $this->configPath($site);
        // backup site configuration
        Cli::runCommand(sprintf('sudo rm %s-phpdev-backup', $backupPath));
    }

    /**
     * Restore backup configuration
     *
     * @param string $site
     * @return void
     */
    public function restoreBackupConfiguration(string $site): void
    {
        Helper::info(sprintf('Restore %s backup configuration', $site));
        // site configuration path
        $configPath = $this->configPath($site);
        // backup site configuration
        Cli::runCommand(sprintf('sudo mv %s-phpdev-backup %s', $configPath, $configPath));
    }

    /**
     * Determine is backup configuration exists
     *
     * @param string $site
     * @return boolean
     */
    public function isBackupExists(string $site): bool
    {
        return file_exists($this->configPath($site) . '-phpdev-backup');
    }
}

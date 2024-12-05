<?php

namespace PhpDev\App;

use PhpDev\Facades\Configuration;
use PhpDev\Tools\Filesystem;

use function PhpDev\info;
use function PhpDev\user;

class PhpFpm
{
    /**
     * Class constructor
     */
    public function __construct(
        protected Brew $brew,
        protected Filesystem $file
    ) {
        //
    }

    /**
     * Get service name
     *
     * @param string $version
     * @return string
     */
    public function serviceName(string $version): string
    {
        return sprintf('php@%s', $version);
    }

    /**
     * Install and configure PhpFpm.
     */
    public function install(): void
    {
        info('Installing and configuring phpfpm...');

        $this->createConfigurationFiles($this->getVersion());

        $this->restart();
    }

    /**
     * start the PHP FPM process.
     */
    public function start(?string $phpVersion = null): void
    {
        info('Starting phpfpm...');
        // get services
        $services = is_null($phpVersion) ? $this->utilizedPhpVersions() : $this->serviceName($phpVersion);
        // start services
        $this->brew->startService($services);
    }

    /**
     * Stop the PHP FPM process.
     */
    public function stop(): void
    {
        info('Stopping phpfpm...');
        // stop service
        $this->brew->stopService($this->utilizedPhpVersions());
    }

    /**
     * Restart the PHP FPM process (if one specified) or processes (if none specified).
     */
    public function restart(?string $phpVersion = null): void
    {
        // get services
        $services = is_null($phpVersion) ? $this->utilizedPhpVersions() : $this->serviceName($phpVersion);
        // restart service
        $this->brew->restartService($services);
    }

    /**
     * Get php version
     *
     * @param string|null $php
     * @return string
     */
    public function getVersion(?string $php = null): string
    {
        // if empty, use current php version
        if (empty($php)) {
            return sprintf('%s.%s', PHP_MAJOR_VERSION, PHP_MINOR_VERSION);
        }
        // parse the given php version
        $version = explode('.', $php);
        // return php version
        return sprintf('%s.%s', $version[0], $version[1] ?? 0);
    }

    /**
     * Ensure php version installed
     *
     * @param string $version
     * @return boolean
     */
    public function installed(string $version): bool
    {
        return $this->brew->installed($this->serviceName($version));
    }

    /**
     * Create (or re-create) the PHP FPM configuration files.
     *
     * Writes FPM config file, pointing to the correct .sock file, and log and ini files.
     */
    public function createConfigurationFiles(string $phpVersion): void
    {
        info("Updating PHP configuration for {$phpVersion}...");

        $fpmConfigFile = $this->fpmConfigPath($phpVersion);

        $this->file->ensureDirExists(dirname($fpmConfigFile), user());

        // rename (to disable) old FPM Pool configuration
        $oldFile = dirname($fpmConfigFile) . '/www.conf';
        if (file_exists($oldFile)) {
            rename($oldFile, $oldFile . '-phpdev-backup');
        }

        // Create FPM Config File from stub
        $contents = str_replace(
            ['PHPDEV_USER', 'PHPDEV_GROUP', 'PHPDEV_PHP_FPM_PATH', 'phpdev.sock'],
            [user(), user(), PHPDEV_HOME_PATH, $this->fpmSockName($phpVersion)],
            $this->file->getStub('phpfpm.conf')
        );
        $this->file->put($fpmConfigFile, $contents);
    }

    /**
     * Get the path to the FPM configuration file for the current PHP version.
     */
    public function fpmConfigPath(string $phpVersion): string
    {
        return sprintf(
            '%s/etc/php/%s/php-fpm.d/phpdev-fpm.conf',
            PHPDEV_BREW_PATH,
            $phpVersion
        );
    }

    /**
     * Get FPM sock file name for a given PHP version.
     */
    public function fpmSockName(?string $phpVersion = null): string
    {
        $versionInteger = preg_replace('~[^\d]~', '', $phpVersion);

        return sprintf('phpdev%s.sock', $versionInteger);
    }

    /**
     * Get FPM sock file path for a given PHP version.
     */
    public function fpmSockPath(?string $phpVersion = null): string
    {
        if (is_null($phpVersion)) {
            $phpVersion = $this->getVersion();
        }

        return sprintf(PHPDEV_HOME_PATH . '/%s', $this->fpmSockName($phpVersion));
    }

    public function utilizedPhpVersions(): array
    {
        $config = Configuration::read();

        return array_map(function ($phpVersion) {
            return $this->serviceName($phpVersion);
        }, $config['php']);
    }
}

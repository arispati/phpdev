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
     * Install and configure PhpFpm.
     */
    public function install(): void
    {
        info('Installing and configuring phpfpm...');

        $this->createConfigurationFiles($this->brew->linkedPhp());

        $this->restart();
    }

    /**
     * start the PHP FPM process.
     */
    public function start(): void
    {
        info('Starting phpfpm...');
        $this->brew->startService($this->utilizedPhpVersions());
    }

    /**
     * Stop the PHP FPM process.
     */
    public function stop(): void
    {
        info('Stopping phpfpm...');
        $this->brew->stopService($this->utilizedPhpVersions());
    }

    /**
     * Restart the PHP FPM process (if one specified) or processes (if none specified).
     */
    public function restart(?string $phpVersion = null): void
    {
        $this->brew->restartService($phpVersion ?: $this->utilizedPhpVersions());
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
        return $this->brew->installed(sprintf('php@%s', $version));
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
    public function fpmConfigPath(?string $phpVersion = null): string
    {
        if (! $phpVersion) {
            $phpVersion = $this->brew->linkedPhp();
        }

        $versionNormalized = $this->normalizePhpVersion($phpVersion === 'php' ? Brew::LATEST_PHP_VERSION : $phpVersion);
        $versionNormalized = preg_replace('~[^\d\.]~', '', $versionNormalized);

        return PHPDEV_BREW_PATH . "/etc/php/{$versionNormalized}/php-fpm.d/phpdev-fpm.conf";
    }

    /**
     * Get FPM sock file name for a given PHP version.
     */
    public function fpmSockName(?string $phpVersion = null): string
    {
        $versionInteger = preg_replace('~[^\d]~', '', $phpVersion);

        return "phpdev{$versionInteger}.sock";
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

    /**
     * If passed php7.4, or php74, 7.4, or 74 formats, normalize to php@7.4 format.
     */
    public function normalizePhpVersion(?string $version): string
    {
        return preg_replace('/(?:php@?)?([0-9+])(?:.)?([0-9+])/i', 'php@$1.$2', (string) $version);
    }

    public function utilizedPhpVersions(): array
    {
        $config = Configuration::read();

        return array_map(function ($phpVersion) {
            return sprintf('php@%s', $phpVersion);
        }, $config['php']);
    }
}

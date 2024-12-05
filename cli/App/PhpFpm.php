<?php

namespace PhpDev\App;

use PhpDev\Helper\File;
use PhpDev\Helper\Helper;

class PhpFpm
{
    /**
     * Class constructor
     */
    public function __construct(
        protected Brew $brew,
        protected Config $config
    ) {
        //
    }

    /**
     * Start PHP FPM service
     *
     * @param string|null $phpVersion
     * @return void
     */
    public function start(?string $phpVersion = null): void
    {
        Helper::info('Starting phpfpm...');
        // get services
        $services = is_null($phpVersion) ? $this->utilizedPhpVersions() : $this->serviceName($phpVersion);
        // start services
        $this->brew->startService($services);
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
            return PHPDEV_PHP_VERSION;
        }
        // parse the given php version
        [$major, $minor] = explode('.', $php);
        // return php version
        return sprintf('%s.%s', $major, $minor ?? 0);
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
     * Create (or re-create) the PHP FPM configuration files.
     *
     * @param string $phpVersion
     * @return void
     */
    public function createConfigurationFiles(string $phpVersion): void
    {
        Helper::info("Updating PHP configuration for {$phpVersion}...");

        $fpmConfigFile = $this->fpmConfigPath($phpVersion);

        File::ensureDirExists(dirname($fpmConfigFile));

        // rename (to disable) old FPM Pool configuration
        $oldFile = dirname($fpmConfigFile) . '/www.conf';
        if (file_exists($oldFile)) {
            rename($oldFile, $oldFile . '-phpdev-backup');
        }

        // parsing stub
        $user = PHPDEV_USER;
        $contents = str_replace(
            ['PHPDEV_USER', 'PHPDEV_GROUP', 'PHPDEV_PHP_FPM_PATH', 'phpdev.sock'],
            [$user, $user, PHPDEV_HOME_PATH, $this->fpmSockName($phpVersion)],
            File::getStub('phpfpm.conf')
        );
        // Create FPM Config File from stub
        File::put($fpmConfigFile, $contents);
    }

    /**
     * Get the path to the FPM configuration file for the current PHP version.
     *
     * @param string $phpVersion
     * @return string
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
     *
     * @param string $phpVersion
     * @return string
     */
    public function fpmSockName(string $phpVersion): string
    {
        $versionInteger = preg_replace('~[^\d]~', '', $phpVersion);

        return sprintf('phpdev%s.sock', $versionInteger);
    }

    /**
     * Get FPM sock file path for a given PHP version.
     *
     * @param string $phpVersion
     * @return string
     */
    public function fpmSockPath(string $phpVersion): string
    {
        return sprintf(PHPDEV_HOME_PATH . '/%s', $this->fpmSockName($phpVersion));
    }

    /**
     * Utilized used PHP versions
     *
     * @return array
     */
    public function utilizedPhpVersions(): array
    {
        $config = $this->config->read();

        return array_map(function ($phpVersion) {
            return $this->serviceName($phpVersion);
        }, $config['php']);
    }
}

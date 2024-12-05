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
     * Install and configure PhpFpm
     *
     * @return void
     */
    public function install(): void
    {
        Helper::info('Installing and configuring PHP FPM');
        // define variable
        $phpConfig = $this->config->read('php');
        $currentPhp = $this->getVersion();
        // update default config
        $this->config->updateKey('default', [
            'php' => $currentPhp
        ]);
        // validate current php version
        if (! in_array($currentPhp, $phpConfig)) {
            $this->config->addPhp($currentPhp);
        }
        // synch php version
        $unusedPhp = $this->config->synchPhp();
        $phpConfig = $this->config->read('php');
        // iterate php version
        foreach ($phpConfig as $php) {
            $this->createConfigurationFiles($php);
        }
        Helper::write();
        // restart php fpm
        $this->restart();
        // stop unused php version if any
        if (! empty($unusedPhp)) {
            Helper::info(PHP_EOL . 'There is an unused PHP FPM, stop it service.');
            // new line
            PhpFpm::stop($unusedPhp);
        }
    }

    /**
     * Start PHP FPM service
     *
     * @param string|null $phpVersion
     * @return void
     */
    public function start(?string $phpVersion = null): void
    {
        Helper::info('Starting PHP FPM');
        // get services
        $services = is_null($phpVersion) ? $this->utilizedPhpVersions() : $this->serviceName($phpVersion);
        // start services
        $this->brew->startService($services);
    }

    /**
     * Stop PHP FPM service
     *
     * @param string|array|null $phpVersion
     * @return void
     */
    public function stop(string|array|null $phpVersion = null): void
    {
        Helper::info('Stopping PHP FPM');
        // wrap to array
        $phpVersion = is_null($phpVersion) ? $this->utilizedPhpVersions() : $phpVersion;
        $phps = is_array($phpVersion) ? $phpVersion : [$phpVersion];
        // get services
        $services = array_map(function ($php) {
            return $this->serviceName($php);
        }, $phps);
        // stop services
        $this->brew->stopService($services);
    }

    /**
     * Restart PHP FPM service
     *
     * @param string|array|null $phpVersion
     * @return void
     */
    public function restart(string|array|null $phpVersion = null): void
    {
        Helper::info('Restarting PHP FPM');
        // wrap to array
        $phpVersion = is_null($phpVersion) ? $this->utilizedPhpVersions() : $phpVersion;
        $phps = is_array($phpVersion) ? $phpVersion : [$phpVersion];
        // get services
        $services = array_map(function ($php) {
            return $this->serviceName($php);
        }, $phps);
        // stop services
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
        if (Helper::startWith($version, 'php@')) {
            return $version;
        }

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
        Helper::info("Updating PHP {$phpVersion} configuration");

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

        // Create other config files from stubs
        $destDir = dirname(dirname($fpmConfigFile)) . '/conf.d';
        File::ensureDirExists($destDir);

        File::put(
            $destDir . '/php-memory-limits.ini',
            File::getStub('php-memory-limits.ini')
        );

        $logFileName = sprintf('php%s-fpm.log', preg_replace('~[^\d]~', '', $phpVersion));
        $contents = str_replace(
            ['PHPDEV_HOME_PATH', 'PHPDEV_PHP_FPM_LOG'],
            [PHPDEV_HOME_PATH, $logFileName],
            File::getStub('phpfpm-error-log.ini')
        );
        File::put($destDir . '/error_log.ini', $contents);

        // Create log directory and file
        File::ensureDirExists(PHPDEV_HOME_PATH . '/log/php-fpm');
        File::touch(sprintf('%s/log/php-fpm/%s', PHPDEV_HOME_PATH, $logFileName));
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

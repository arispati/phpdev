<?php

namespace PhpDev\App;

use PhpDev\Helper\File;
use PhpDev\Helper\Helper;

class Config
{
    /**
     * Initiate configuration
     *
     * @return void
     */
    public function init(): void
    {
        $this->createConfigurationDirectory();
        $this->ensureBaseConfiguration();
    }

    /**
     * Ensure site exists
     *
     * @param string $site
     * @return boolean
     */
    public function siteExists(string $site): bool
    {
        return is_array($this->siteGet($site));
    }

    /**
     * Get site config
     *
     * @param string $site
     * @return array|null
     */
    public function siteGet($site): ?array
    {
        $config = $this->read('sites');

        return isset($config[$site]) ? $config[$site] : null;
    }

    /**
     * Ensure PHP exists
     *
     * @param string $php
     * @return boolean
     */
    public function phpExists($php): bool
    {
        $config = $this->read('php');

        return in_array($php, $config);
    }

    /**
     * Add PHP config
     *
     * @param string $php
     * @return void
     */
    public function addPhp(string $php): void
    {
        // get php config
        $config = $this->read('php');
        // validate php version
        if (array_search($php, $config) === false) {
            // add new config
            $newConfig = array_merge($config, [$php]);
            // desc sort config
            rsort($newConfig);
            // update config
            $this->updateKey('php', $newConfig);
        }
    }

    /**
     * Remove PHP config
     *
     * @param string|array $php
     * @return void
     */
    public function removePhp(string|array $php): void
    {
        // get php config
        $config = $this->read('php');
        // wrap to array
        $php = is_array($php) ? $php : [$php];
        // iterate php
        foreach ($php as $item) {
            // search php
            $index = array_search($item, $config);
            // remove config
            if ($index !== false) {
                unset($config[$index]);
            }
        }
        // desc sort config
        rsort($config);
        // update config
        $this->updateKey('php', $config);
    }

    /**
     * Synchronize PHP config with the sites
     *
     * @return array List of unused php version
     */
    public function synchPhp(): array
    {
        $unused = [];
        // get config
        $config = $this->read();
        // iterate available php
        foreach ($config['php'] as $php) {
            $sites = array_filter($config['sites'], function ($item) use ($php) {
                return $item['php'] == $php;
            });
            // validate the filtered sites
            if (empty($sites)) {
                // validate php is default config or not
                if ($config['default']['php'] != $php) {
                    $unused[] = $php;
                }
            }
        }
        // update config if found unused php version
        if (! empty($unused)) {
            $this->removePhp($unused);
        }
        // return unused php version
        return $unused;
    }

    /**
     * Add site
     *
     * @param string $type
     * @param string $site
     * @param string $path
     * @param string|null $php
     * @param string|null $ssl
     * @return void
     */
    public function addSite(string $type, string $site, string $path, ?string $php = null, ?string $ssl = null): void
    {
        // get sites config
        $config = $this->read('sites');
        // add new config
        $config[$site] = [
            'name' => $site,
            'path' => $path,
            'type' => $type,
            'php' => $php ?? '-',
            'ssl' => (bool) $ssl,
            'ssl_path' => $ssl
        ];
        // update config
        $this->updateKey('sites', $config);
        // add php version to config
        if (! empty($php)) {
            $this->addPhp($php);
        }
    }

    /**
     * Remove site
     *
     * @param string $site
     * @return void
     */
    public function removeSite(string $site): void
    {
        // get sites config
        $config = $this->read('sites');
        // validate site
        if (isset($config[$site])) {
            // remove site
            unset($config[$site]);
            // update config
            $this->updateKey('sites', $config);
        }
    }

    /**
     * Create the Valet configuration directory.
     *
     * @return void
     */
    public function createConfigurationDirectory(): void
    {
        File::ensureDirExists(PHPDEV_HOME_PATH);
    }

    /**
     * Ensure the base initial configuration has been installed.
     *
     * @return void
     */
    public function ensureBaseConfiguration(): void
    {
        if (! File::exists($this->path())) {
            $this->write([
                'php' => [],
                'default' => [
                    'php' => null
                ],
                'sites' => []
            ]);
        }
    }

    /**
     * Read the configuration file as JSON.
     *
     * @param string|null $key
     * @return array
     */
    public function read(?string $key = null): array
    {
        if (! File::exists($this->path())) {
            $this->ensureBaseConfiguration();
        }

        $config = json_decode(File::get($this->path()), true, 512, JSON_THROW_ON_ERROR);

        return is_null($key) ? $config : $config[$key];
    }

    /**
     * Update a specific key in the configuration file.
     *
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public function updateKey(string $key, mixed $value): array
    {
        return Helper::tab($this->read(), function (&$config) use ($key, $value) {
            // apply to config
            $config[$key] = $value;
            // write configuration
            $this->write($config);
        });
    }

    /**
     * Write the given configuration to disk.
     */
    public function write(array $config): void
    {
        File::put($this->path(), json_encode(
            $config,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ) . PHP_EOL);
    }

    /**
     * Get the configuration file path.
     *
     * @return string
     */
    public function path(): string
    {
        return PHPDEV_CONFIG_PATH;
    }
}

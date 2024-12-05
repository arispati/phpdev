<?php

namespace PhpDev\App;

use PhpDev\Helper\File;
use PhpDev\Helper\Helper;

class Config
{
    /**
     * Class constructor
     *
     * @param File $file
     */
    public function __construct(
        protected File $file,
        protected PhpFpm $php
    ) {
        //
    }

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
     * Create the Valet configuration directory.
     *
     * @return void
     */
    public function createConfigurationDirectory(): void
    {
        $this->file->ensureDirExists(PHPDEV_HOME_PATH);
    }

    /**
     * Ensure the base initial configuration has been installed.
     *
     * @return void
     */
    public function ensureBaseConfiguration(): void
    {
        if (! $this->file->exists($this->path())) {
            $this->write([
                'php' => [$this->php->getVersion()],
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
        if (! $this->file->exists($this->path())) {
            $this->ensureBaseConfiguration();
        }

        $config = json_decode($this->file->get($this->path()), true, 512, JSON_THROW_ON_ERROR);

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
            $config[$key] = $value;

            $this->write($config);
        });
    }

    /**
     * Write the given configuration to disk.
     */
    public function write(array $config): void
    {
        $this->file->put($this->path(), json_encode(
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

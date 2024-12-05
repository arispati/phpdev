<?php

namespace PhpDevBackup\App;

use PhpDevBackup\Tools\Filesystem;

use function PhpDevBackup\tap;
use function PhpDevBackup\user;

class Configuration
{
    public function __construct(
        protected Filesystem $file,
        protected PhpFpm $php
    ) {
        //
    }

    public function install()
    {
        $this->createConfigurationDirectory();
        $this->ensureBaseConfiguration();
    }

    /**
     * Create the Valet configuration directory.
     */
    public function createConfigurationDirectory(): void
    {
        $this->file->ensureDirExists(PHPDEV_HOME_PATH, user());
    }

    /**
     * Ensure the base initial configuration has been installed.
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
     */
    public function updateKey(string $key, mixed $value): array
    {
        return tap($this->read(), function (&$config) use ($key, $value) {
            $config[$key] = $value;

            $this->write($config);
        });
    }

    /**
     * Write the given configuration to disk.
     */
    public function write(array $config): void
    {
        $this->file->putAsUser($this->path(), json_encode(
            $config,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ) . PHP_EOL);
    }

    /**
     * Get the configuration file path.
     */
    public function path(): string
    {
        return PHPDEV_CONFIG_PATH;
    }
}
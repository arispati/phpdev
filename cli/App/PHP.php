<?php

namespace Arispati\Phpdev\App;

use Arispati\Phpdev\Tools\CommandLine;

class PHP
{
    protected Brew $brew;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->brew = new Brew();
    }

    /**
     * Get php version
     *
     * @param string|null $php
     * @return string
     */
    public function getVersion(?string $php): string
    {
        // if empty, use current php version
        if (empty($php)) {
            $php = sprintf('%s.%s', PHP_MAJOR_VERSION, PHP_MINOR_VERSION);
            goto return_version;
        }

        $splitVersion = explode('.', $php);

        return $php ? $php : sprintf('%s.%s', PHP_MAJOR_VERSION, PHP_MINOR_VERSION);

        return_version:
        return $php;
    }

    public function installed(string $version): bool
    {
        return $this->brew->installed(sprintf('php@%s', $version));
    }
}

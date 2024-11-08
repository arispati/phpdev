<?php

namespace Arispati\Phpdev\App;

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
}

<?php

namespace PhpDev\App;

use PhpDev\Tools\CommandLine;
use PhpDev\Tools\Filesystem;
use DomainException;
use Illuminate\Support\Collection;

use function PhpDev\info;
use function PhpDev\starts_with;

class Brew
{
    // This is the array of PHP versions that Valet will attempt to install/configure when requested
    public const SUPPORTED_PHP_VERSIONS = [
        'php',
        'php@8.3',
        'php@8.2',
        'php@8.1',
        'php@8.0',
        'php@7.4',
        'php@7.3',
        'php@7.2',
        'php@7.1',
    ];

    // Update this LATEST and the following LIMITED array when PHP versions are released or retired
    // We specify a numbered version here even though Homebrew links its generic 'php' alias to it
    public const LATEST_PHP_VERSION = 'php@8.3';

    /**
     * Class constructor
     */
    public function __construct(
        protected CommandLine $cli,
        protected Filesystem $file
    ) {
        //
    }

    /**
     * Ensure the formula exists
     *
     * @param string $formula
     * @return boolean
     */
    public function installed(string $formula): bool
    {
        $result = $this->cli->runCommand(sprintf('brew info %s --json=v2', $formula));
        // if an error occur
        if (starts_with($result, 'Error: No')) {
            return false;
        }
        // detail result
        $detail = json_decode($result, true);
        if (! empty($detail['formulae'])) {
            return ! empty($detail['formulae'][0]['installed']);
        }
        // return
        return false;
    }

    /**
     * Restart the given Homebrew services.
     */
    public function restartService($services): void
    {
        $services = is_array($services) ? $services : func_get_args();

        foreach ($services as $service) {
            if ($this->installed($service)) {
                info("Restarting {$service}...");

                // stop service
                $this->cli->quietly('brew services stop ' . $service);
                // start service
                $this->cli->quietly('brew services start ' . $service);
            }
        }
    }

    /**
     * Determine if php is currently linked.
     */
    public function hasLinkedPhp(): bool
    {
        return $this->file->isLink(PHPDEV_BREW_PATH . '/bin/php');
    }

    /**
     * Get the linked php parsed.
     */
    public function getParsedLinkedPhp(): array
    {
        if (! $this->hasLinkedPhp()) {
            throw new DomainException('Homebrew PHP appears not to be linked.');
        }

        $resolvedPath = $this->file->readLink(PHPDEV_BREW_PATH . '/bin/php');

        return $this->parsePhpPath($resolvedPath);
    }

    /**
     * Determine which version of PHP is linked in Homebrew.
     */
    public function linkedPhp(): string
    {
        $matches = $this->getParsedLinkedPhp();
        $resolvedPhpVersion = $matches[3] ?: $matches[2];

        return $this->supportedPhpVersions()->first(
            function ($version) use ($resolvedPhpVersion) {
                return $this->arePhpVersionsEqual($resolvedPhpVersion, $version);
            },
            function () use ($resolvedPhpVersion) {
                throw new DomainException("Unable to determine linked PHP when parsing '$resolvedPhpVersion'");
            }
        );
    }

    /**
     * Parse homebrew PHP Path.
     */
    public function parsePhpPath(string $resolvedPath): array
    {
        /**
         * Typical homebrew path resolutions are like:
         * "../Cellar/php@7.4/7.4.13/bin/php"
         * or older styles:
         * "../Cellar/php/7.4.9_2/bin/php
         * "../Cellar/php55/bin/php.
         */
        preg_match('~\w{3,}/(php)(@?\d\.?\d)?/(\d\.\d)?([_\d\.]*)?/?\w{3,}~', $resolvedPath, $matches);

        return $matches;
    }

    /**
     * Get a list of supported PHP versions.
     */
    public function supportedPhpVersions(): Collection
    {
        return collect(static::SUPPORTED_PHP_VERSIONS);
    }

    /**
     * Check if two PHP versions are equal.
     */
    public function arePhpVersionsEqual(string $versionA, string $versionB): bool
    {
        $versionANormalized = preg_replace('/[^\d]/', '', $versionA);
        $versionBNormalized = preg_replace('/[^\d]/', '', $versionB);

        return $versionANormalized === $versionBNormalized;
    }
}

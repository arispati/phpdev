<?php

namespace PhpDev\App;

use PhpDev\Helper\Cli;
use PhpDev\Helper\Helper;

class Site
{
    /**
     * Get site name
     *
     * @param string|null $name
     * @param string|null $tld
     * @return string
     */
    public function name(?string $name = null, ?string $tld = null): string
    {
        // is name already given
        if (! empty($name)) {
            goto site_name;
        }
        // set name by directory name
        $name = basename(PHPDEV_CURRENT_DIR_PATH);
        // return site name
        site_name:
        // normalized site name
        $tld = is_null($tld) ? PHPDEV_TLD : ltrim($tld, '.');
        $tld = sprintf('.%s', $tld);
        if (Helper::endWith($name, $tld)) {
            $name = substr($name, 0, -strlen($tld));
        }
        // return site name with TLD
        return $name . $tld;
    }

    /**
     * Get site path
     *
     * @param string|null $path
     * @return string
     */
    public function path(?string $path = null): string
    {
        // check the given path
        if (Helper::startWith($path, ['.', './', '/'])) {
            $path = realpath($path);
        } else {
            $path = realpath(sprintf('%s/%s', PHPDEV_CURRENT_DIR_PATH, $path));
        }
        // validate path
        if (! $path) {
            throw new \Exception('Invalid path');
        }
        return rtrim($path, '/');
    }
}

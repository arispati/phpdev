<?php

namespace PhpDev\App;

use PhpDev\Helper\Helper;

class Site
{
    /**
     * Get site name
     *
     * @param string|null $name
     * @return string
     */
    public function name(?string $name = null): string
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
        $tld = sprintf('.%s', PHPDEV_TLD);
        if (Helper::endWith($name, $tld)) {
            $name = substr($name, 0, -strlen($tld));
        }
        // return site name with TLD
        return sprintf('%s.%s', $name, PHPDEV_TLD);
    }

    /**
     * Get site path
     *
     * @param string|null $path
     * @return string
     */
    public function path(?string $path = null): string
    {
        return rtrim(sprintf('%s/%s', PHPDEV_CURRENT_DIR_PATH, $path), '/');
    }
}

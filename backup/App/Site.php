<?php

namespace PhpDev\App;

use PhpDev\Facades\Configuration;

use function PhpDev\ends_with;

class Site
{
    /**
     * Get site name
     *
     * @param string|null $name
     * @return string
     */
    public function name(?string $name): string
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
        if (ends_with($name, $tld)) {
            $name = substr($name, 0, -strlen($tld));
        }
        // return site name with TLD
        return sprintf('%s.%s', $name, PHPDEV_TLD);
    }

    public function links()
    {
        $sites = Configuration::read('sites');
    }
}

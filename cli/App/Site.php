<?php

namespace Arispati\Phpdev\App;

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
            return $name;
        }
        // set name by directory name
        $name = basename(PHPDEV_CURRENT_DIR_PATH);
        // return site name
        return sprintf('%s.%s', $name, PHPDEV_TLD);
    }
}

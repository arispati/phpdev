<?php

namespace Arispati\Phpdev\App;

use Arispati\Phpdev\Tools\Filesystem;
use Silly\Command\Command as SillyCommand;

use function Arispati\Phpdev\output;
use function Arispati\Phpdev\warning;

class Command
{
    /**
     * Class constructor
     */
    public function __construct(
        protected Site $site,
        protected PhpFpm $php,
        protected Filesystem $file
    ) {
        //
    }

    /**
     * Link to phpdev
     *
     * @param string|null $path
     * @param string|null $site
     * @param string|null $php
     * @return void
     */
    public function link($path, $site, $php)
    {
        $site = $this->site->name($site);
        $php = $this->php->getVersion($php);
        // validate php version
        if (! $this->php->installed($php)) {
            output('Error:');
            warning(sprintf('PHP %s not installed yet', $php));
            // end command
            return SillyCommand::FAILURE;
        }
        // var_dump($installed);

        print_r([$path, $site, $php, PHPDEV_CURRENT_DIR_PATH, PHPDEV_DATA_PATH]);
    }
}

<?php

namespace Arispati\Phpdev\App;

use Silly\Command\Command as SillyCommand;
use Symfony\Component\Console\Output\OutputInterface;

class Command
{
    protected Site $site;
    protected PHP $php;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->site = new Site();
        $this->php = new PHP();
    }

    /**
     * Link to phpdev
     *
     * @param string|null $path
     * @param string|null $site
     * @param string|null $php
     * @param OutputInterface $output
     * @return void
     */
    public function link($path, $site, $php, OutputInterface $output)
    {
        $site = $this->site->name($site);
        $php = $this->php->getVersion($php);
        // validate php version
        if (! $this->php->installed($php)) {
            $output->writeln('Error:');
            $output->writeln(sprintf('<fg=red>PHP %s not installed yet</>', $php));
            // end command
            return SillyCommand::FAILURE;
        }
        // var_dump($installed);

        print_r([$path, $site, $php, PHPDEV_CURRENT_DIR_PATH, $php]);
    }
}

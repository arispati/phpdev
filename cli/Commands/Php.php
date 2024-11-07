<?php

namespace Arispati\Phpdev\Commands;

use Arispati\Phpdev\Drivers\CommandLine;
use Symfony\Component\Console\Output\OutputInterface;

class Php
{
    public function __construct(
        protected CommandLine $cli = new CommandLine()
    ) {
        //
    }

    public function handle($action, OutputInterface $output)
    {
        if (in_array($action, ['start', 'stop'])) {
            $this->{$action}($output);
        }
    }

    public function start(OutputInterface $output)
    {
        $output->writeln($this->cli->runCommand('brew services start php'));
    }

    public function stop(OutputInterface $output)
    {
        $output->writeln($this->cli->runCommand('brew services stop php'));
    }
}

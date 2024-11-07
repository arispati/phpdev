<?php

namespace Arispati\Phpdev\Commands;

use Symfony\Component\Console\Output\OutputInterface;

class Hello
{
    public function __invoke(OutputInterface $output)
    {
        $output->writeln('Hello');
    }
}

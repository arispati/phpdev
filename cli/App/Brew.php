<?php

namespace Arispati\Phpdev\App;

use Arispati\Phpdev\Tools\CommandLine;

use function Arispati\Phpdev\starts_with;

class Brew
{
    protected CommandLine $cli;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->cli = new CommandLine();
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
}

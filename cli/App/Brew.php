<?php

namespace PhpDev\App;

use PhpDev\Helper\Cli;
use PhpDev\Helper\Helper;

class Brew
{
    /**
     * Ensure the formula exists
     *
     * @param string $formula
     * @return boolean
     */
    public function installed(string $formula): bool
    {
        $result = Cli::runCommand(sprintf('brew info %s --json=v2', $formula));
        // if an error occur
        if (Helper::startWith($result, 'Error: No')) {
            return false;
        }
        // detail result
        $detail = json_decode($result, true);
        // validate formula
        if (! empty($detail['formulae'])) {
            return ! empty($detail['formulae'][0]['installed']);
        }
        // return
        return false;
    }

    /**
     * Start the given Homebrew services.
     */
    public function startService(string $services): void
    {
        $services = is_array($services) ? $services : func_get_args();

        foreach ($services as $service) {
            if ($this->installed($service)) {
                Helper::info("Starting {$service}...");
                // start service
                Cli::quietly('brew services start ' . $service);
            }
        }
    }
}

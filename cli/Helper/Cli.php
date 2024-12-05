<?php

namespace PhpDev\Tools;

use Symfony\Component\Process\Process;

class Cli
{
    /**
     * Run the given command.
     */
    public function runCommand(string $command, ?callable $onError = null): string
    {
        $onError = $onError ?: function () {
            //
        };

        $process = Process::fromShellCommandline($command);

        $processOutput = '';
        $process->setTimeout(null)->run(function ($type, $line) use (&$processOutput) {
            $processOutput .= $line;
        });

        if ($process->getExitCode() > 0) {
            $onError($process->getExitCode(), $processOutput);
        }

        return $processOutput;
    }
}

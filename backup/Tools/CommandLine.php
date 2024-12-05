<?php

namespace PhpDev\Tools;

use Symfony\Component\Process\Process;

class CommandLine
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

    /**
     * Simple global function to run commands quietly.
     */
    public function quietly(string $command): void
    {
        $this->runCommand($command . ' > /dev/null 2>&1');
    }
}

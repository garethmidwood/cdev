<?php

namespace Creode\System;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class Command
{
    protected function run(
        $command,
        array $options,
        $workingDir,
        $timeout = 3600,
        $liveUpdates = true
    ) {
        array_unshift($options, $command);

        $builder = new ProcessBuilder($options);
        $process = $builder->getProcess();
        $process->setTimeout($timeout);
        $process->setWorkingDirectory($workingDir);
        

        if ($liveUpdates) {
            $process->run(
                function ($type, $buffer) {
                    if (Process::ERR === $type) {
                        echo $buffer;
                    } else {
                        echo $buffer;
                    }
                }
            );
        } else {
            $process->run();
        }

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}

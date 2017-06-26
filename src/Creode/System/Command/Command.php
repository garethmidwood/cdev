<?php

namespace Creode\System\Command;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Exception\ProcessFailedException;

abstract class Command
{
    protected function run($command, array $options, $workingDir)
    {
        array_unshift($options, $command);

        $builder = new ProcessBuilder($options);
        $process = $builder->getProcess();
        $process->setWorkingDirectory($workingDir);
        
        $process->run();

        // // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}

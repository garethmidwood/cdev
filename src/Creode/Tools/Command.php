<?php

namespace Creode\Tools;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\ProcessFailedException;

abstract class Command
{
    protected function run($command)
    {
        echo 'running ' . $command . PHP_EOL;
        $builder = new ProcessBuilder(array($command));
        $process = $builder->getProcess();
        
        $process->mustRun();

        // $process->run();

        // // executes after the command finishes
        // if (!$process->isSuccessful()) {
        //     throw new ProcessFailedException($process);
        // }

        return $process->getOutput();
    }
}

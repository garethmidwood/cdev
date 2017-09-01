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
        $liveUpdates = true,
        $inputFile = false
    ) {
        echo '>>> Running `' . $command . ' ' . implode(' ', $options) . '`' . PHP_EOL;
        array_unshift($options, $command);

        $builder = new ProcessBuilder($options);
        $process = $builder->getProcess();
        $process->setTimeout($timeout);
        $process->setWorkingDirectory($workingDir);
        $process->setPty(true);
        
        if ($inputFile) {
            $process->setInput($inputFile);
        }
        
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


    /**
     * execute an external command
     *
     * @param string $command
     */
    protected function runExternalCommand(
        $command,
        array $options,
        $workingDir
    ) {
        $cmd = $command . ' ' . implode(' ', $options);

        echo '>>> Running `' . $cmd . '`' . PHP_EOL;

        $descriptorSpec = array(
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        );

        $pipes = array();

        $process = proc_open($cmd, $descriptorSpec, $pipes, $workingDir);

        if (is_resource($process)) {
            proc_close($process);
        }
    }
}

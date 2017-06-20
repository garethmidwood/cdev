<?php
namespace Creode\Tools\Docker;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Sync
{
    public function start()
    {
        /**
         * 
         * DO THIS TO ALL FUNCTIONS
         * 
         */
        $process = new Process('docker-sync start');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

        return 'docker-sync start completed';
    }

    public function stop()
    {
        return 'docker-sync stop';
    }

    public function clean()
    {
        return 'docker-sync clean';
    }

    public function sync()
    {
        return 'docker-sync sync';
    }
}

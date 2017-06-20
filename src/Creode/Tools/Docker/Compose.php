<?php
namespace Creode\Tools\Docker;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Compose
{
    public function up()
    {
        /**
         * 
         * DO THIS TO ALL FUNCTIONS
         * 
         */
        $process = new Process('docker-compose up');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

        return 'docker-compose up completed';
    }

    public function stop()
    {
        return 'docker-compose stop';
    }

    public function rm()
    {
        return 'docker-compose rm';
    }
}

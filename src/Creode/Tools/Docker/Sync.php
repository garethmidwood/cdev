<?php
namespace Creode\Tools\Docker;

use Creode\Tools\SystemCommand;

class Sync extends SystemCommand
{
    public function start()
    {
        $this->run('docker-sync start');

        return 'docker-sync start completed';
    }

    public function stop()
    {
        $this->run('docker-sync stop');

        return 'docker-sync stop completed';
    }

    public function clean()
    {
        $this->run('docker-sync clean');

        return 'docker-sync clean completed';
    }

    public function sync()
    {
        $this->run('docker-sync sync');

        return 'docker-sync sync completed';
    }
}

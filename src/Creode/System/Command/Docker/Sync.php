<?php
namespace Creode\System\Command\Docker;

use Creode\System\Command\Command;

class Sync extends Command
{
    public function start($path)
    {
        $this->run('docker-sync', ['start'], $path);

        return 'docker-sync start completed';
    }

    public function stop($path)
    {
        $this->run('docker-sync', ['stop'], $path);

        return 'docker-sync stop completed';
    }

    public function clean($path)
    {
        $this->run('docker-sync', ['clean'], $path);

        return 'docker-sync clean completed';
    }

    public function sync($path)
    {
        $this->run('docker-sync', ['sync'], $path);

        return 'docker-sync sync completed';
    }
}

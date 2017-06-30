<?php
namespace Creode\System\Command\Docker;

use Creode\System\Command\Command;

class Compose extends Command
{
    public function up($path)
    {
        $this->run('docker-compose', ['up'], $path);

        return 'docker-compose up completed';
    }

    public function stop($path)
    {
        $this->run('docker-compose', ['stop'], $path);

        return 'docker-compose stop completed';
    }

    public function rm($path)
    {
        $this->run('docker-compose', ['rm', '-f'], $path);

        return 'docker-compose rm completed';
    }
}

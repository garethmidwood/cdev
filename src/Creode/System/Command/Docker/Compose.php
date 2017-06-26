<?php
namespace Creode\System\Command\Docker;

use Creode\System\Command\Command;

class Compose extends Command
{
    public function up()
    {
        $this->run('docker-compose up');

        return 'docker-compose up completed';
    }

    public function stop()
    {
        $this->run('docker-compose stop');

        return 'docker-compose stop completed';
    }

    public function rm()
    {
        $this->run('docker-compose rm');

        return 'docker-compose rm completed';
    }
}

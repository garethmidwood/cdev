<?php
namespace Creode\System\Docker;

use Creode\System\Command;

class Compose extends Command
{
    const COMMAND = 'docker-compose';

    public function up($path)
    {
        $this->run(self::COMMAND, ['up'], $path);

        return 'docker-compose up completed';
    }

    public function stop($path)
    {
        $this->run(self::COMMAND, ['stop'], $path);

        return 'docker-compose stop completed';
    }

    public function rm($path)
    {
        $this->run(self::COMMAND, ['rm', '-f'], $path);

        return 'docker-compose rm completed';
    }

    public function runCmd($path, $command, $options)
    {
        $this->run(self::COMMAND, $options, $path);        
    }
}

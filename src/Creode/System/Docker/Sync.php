<?php
namespace Creode\System\Docker;

use Creode\System\Command;

class Sync extends Command
{
    const COMMAND = 'docker-sync';
    
    public function start($path)
    {
        $this->run(self::COMMAND, ['start'], $path);

        return 'docker-sync start completed';
    }

    public function stop($path)
    {
        $this->run(self::COMMAND, ['stop'], $path);

        return 'docker-sync stop completed';
    }

    public function clean($path)
    {
        $this->run(self::COMMAND, ['clean'], $path);

        return 'docker-sync clean completed';
    }

    public function sync($path)
    {
        $this->run(self::COMMAND, ['sync'], $path);

        return 'docker-sync sync completed';
    }
}

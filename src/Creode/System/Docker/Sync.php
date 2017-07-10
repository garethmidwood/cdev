<?php
namespace Creode\System\Docker;

use Creode\System\Command;

class Sync extends Command
{
    const COMMAND = 'docker-sync';
    const FILE = 'docker-sync.yml';

    /**
     * @var boolean
     */
    private $_configExists = false;

    public function __construct() 
    {
        $this->_configExists = file_exists(self::FILE);
    }
    
    public function start($path)
    {
        if (!$this->_configExists) {
            return self::FILE . ' not found.';
        }

        $this->run(self::COMMAND, ['start'], $path);

        return self::COMMAND . ' start completed';
    }

    public function stop($path)
    {
        if (!$this->_configExists) {
            return self::FILE . ' not found.';
        }

        $this->run(self::COMMAND, ['stop'], $path);

        return self::COMMAND . ' stop completed';
    }

    public function clean($path)
    {
        if (!$this->_configExists) {
            return self::FILE . ' not found.';
        }

        $this->run(self::COMMAND, ['clean'], $path);

        return self::COMMAND . ' clean completed';
    }

    public function sync($path)
    {
        if (!$this->_configExists) {
            return self::FILE . ' not found.';
        }

        $this->run(self::COMMAND, ['sync'], $path);

        return self::COMMAND . ' sync completed';
    }
}

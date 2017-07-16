<?php
namespace Creode\Environment\Docker\System\Sync;

use Creode\Cdev\Config;
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
        $this->_configExists = file_exists(Config::CONFIG_DIR . self::FILE);
    }
    
    public function start($path)
    {
        $this->requiresConfig();

        $this->run(self::COMMAND, ['start'], $path);

        return self::COMMAND . ' start completed';
    }

    public function stop($path)
    {
        $this->requiresConfig();

        $this->run(self::COMMAND, ['stop'], $path);

        return self::COMMAND . ' stop completed';
    }

    public function clean($path)
    {
        $this->requiresConfig();

        $this->run(self::COMMAND, ['clean'], $path);

        return self::COMMAND . ' clean completed';
    }

    public function sync($path)
    {
        $this->requiresConfig();

        $this->run(self::COMMAND, ['sync'], $path);

        return self::COMMAND . ' sync completed';
    }

    /**
     * Prevents running of commands that require config when it doesn't exist
     * @throws \Exception
     */
    private function requiresConfig()
    {
        if (!$this->_configExists) {
            throw new \Exception('Config file ' . Config::CONFIG_DIR . self::FILE . ' was not found.');
        }
    }

    /**
     * Generates the config file
     * @return null
     */
    public function generateConfig()
    {
        echo '(NOT REALLY) Generating ' . self::FILE . PHP_EOL;
    }
}

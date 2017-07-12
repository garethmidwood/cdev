<?php
namespace Creode\System\Docker;

use Creode\System\Command;

class Compose extends Command
{
    const COMMAND = 'docker-compose';
    const FILE = 'docker-compose.yml';

    /**
     * @var boolean
     */
    private $_configExists = false;

    public function __construct() 
    {
        $this->_configExists = file_exists(self::FILE);
    }

    public function up($path, $build = false)
    {
        if (!$this->_configExists) {
            return self::FILE . ' not found.';
        }

        $params = ['up'];

        if ($build) {
            array_push($params, '--build');
        }

        $this->run(self::COMMAND, $params, $path);

        return self::COMMAND . ' up completed';
    }

    public function stop($path)
    {
        if (!$this->_configExists) {
            return self::FILE . ' not found.';
        }
        
        $this->run(self::COMMAND, ['stop'], $path);

        return self::COMMAND . ' stop completed';
    }

    public function rm($path)
    {
        if (!$this->_configExists) {
            return self::FILE . ' not found.';
        }
        
        $this->run(self::COMMAND, ['rm', '-f'], $path);

        return self::COMMAND . ' rm completed';
    }

    public function runCmd($path, $command, $options)
    {
        if (!$this->_configExists) {
            return self::FILE . ' not found.';
        }
        
        $this->run(self::COMMAND, $options, $path);        
    }
}

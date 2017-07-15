<?php
namespace Creode\System\Docker;

use Creode\System\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;

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

    public function ssh($path, $user)
    {
        if (!$this->_configExists) {
            return self::FILE . ' not found.';
        }
        
        $this->run(self::COMMAND, ['exec', "--user=$user", 'php', 'bash'], $path);
    }

    public function runCmd($path, $options)
    {
        if (!$this->_configExists) {
            return self::FILE . ' not found.';
        }
        
        try {
            $this->run(self::COMMAND, $options, $path);        
        } catch (ProcessFailedException $e) {
            $process = $e->getProcess();

            if ($process->getExitCode() == 129) {
                echo 'Docker hung up - it\'s probably fine, it does that...' . PHP_EOL . PHP_EOL;
            } else {
                throw $e;
            }
        }
    }
}

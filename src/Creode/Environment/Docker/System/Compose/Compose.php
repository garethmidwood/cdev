<?php
namespace Creode\Environment\Docker\System\Compose;

use Creode\Cdev\Config;
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
        $this->_configExists = file_exists(Config::CONFIG_DIR . self::FILE);
    }

    /**
     * Starts environment
     * @param string $path 
     * @param bool $build 
     * @return string
     */
    public function up($path, $build = false)
    {
        $this->requiresConfig();

        $params = ['up'];

        if ($build) {
            array_push($params, '--build');
        }

        $this->run(self::COMMAND, $params, $path);

        return self::COMMAND . ' up completed';
    }

    /**
     * Stops environment
     * @param string $path 
     * @return string
     */
    public function stop($path)
    {
        $this->requiresConfig();
        
        $this->run(self::COMMAND, ['stop'], $path);

        return self::COMMAND . ' stop completed';
    }

    /**
     * Removes environment
     * @param string $path 
     * @return string
     */
    public function rm($path)
    {
        $this->requiresConfig();
        
        $this->run(self::COMMAND, ['rm', '-f'], $path);

        return self::COMMAND . ' rm completed';
    } 

    /**
     * Connects to running environment
     * @param string $path 
     * @param string $user User to connect as 
     * @return string
     */
    public function ssh($path, $user)
    {
        $this->requiresConfig();
        
        $this->run(self::COMMAND, ['exec', "--user=$user", 'php', 'bash'], $path);
    }

    /**
     * Runs a command on the running environment
     * @param string $path 
     * @param array $options Command and options as array 
     * @return null
     */
    public function runCmd($path, array $options)
    {
        $this->requiresConfig();
        
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

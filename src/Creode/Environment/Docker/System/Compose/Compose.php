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

    /**
     * @var string
     */
    private $_networkName;

    public function __construct() 
    {
        $this->_configExists = file_exists(Config::CONFIG_DIR . self::FILE);
    }

    /**
     * Sets name of the network to create
     * @param string $networkName 
     */
    public function setNetwork($networkName)
    {
        $this->_networkName = $networkName;
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

        $params = [
            '-f',
            Config::CONFIG_DIR . self::FILE,
            '-p',
            $this->_networkName,
            'up'
        ];

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
        
        $this->run(
            self::COMMAND,
            [
                '-f',
                Config::CONFIG_DIR . self::FILE,
                '-p',
                $this->_networkName,
                'stop'
            ],
            $path
        );

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
        
        $this->run(
            self::COMMAND,
            [
                '-f',
                Config::CONFIG_DIR . self::FILE,
                '-p',
                $this->_networkName,
                'rm',
                '-f'
            ],
            $path
        );

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
        
        $this->runExternalCommand(
            self::COMMAND,
            [
                '-f',
                Config::CONFIG_DIR . self::FILE,
                '-p',
                $this->_networkName,
                'exec',
                "--user=$user",
                'php',
                'bash'
            ],
            $path
        );
    }

    /**
     * Shows status of environment
     * @param string $path 
     * @return string
     */
    public function ps($path)
    {
        $this->requiresConfig();
        
        $this->runExternalCommand(
            self::COMMAND,
            [
                '-f',
                Config::CONFIG_DIR . self::FILE,
                '-p',
                $this->_networkName,
                'ps'
            ],
            $path
        );
    }

    /**
     * Connects to database
     * @param string $path 
     * @param string $database Database to connect to
     * @param string $user User to connect as 
     * @param string $password Password for user
     */
    public function dbConnect($path, $database, $user, $password)
    {
        $this->requiresConfig();
        
        $this->runExternalCommand(
            self::COMMAND,
            [
                '-f',
                Config::CONFIG_DIR . self::FILE,
                '-p',
                $this->_networkName,
                'exec',
                'mysql',
                'mysql',
                '-u',
                $user,
                '-p'.$password,
                $database
            ],
            $path
        );
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

        $params = [
            '-f',
            Config::CONFIG_DIR . self::FILE,
            '-p',
            $this->_networkName
        ];

        $params = array_merge($params, $options);
        
        try {
            $this->run(self::COMMAND, $params, $path);        
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
}

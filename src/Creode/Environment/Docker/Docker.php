<?php

namespace Creode\Environment\Docker;

use Creode\Cdev\Config;
use Creode\Environment\Docker\System\Compose\Compose;
use Creode\Environment\Docker\System\Docker as SystemDocker;
use Creode\Environment\Docker\System\Sync\Sync;
use Creode\Environment\Environment;
use Creode\Framework\Framework;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;



class Docker extends Environment
{
    const NAME = 'docker';
    const LABEL = 'Docker';
    const COMMAND_NAMESPACE = 'docker';
    
    /**
     * @var SystemDocker
     */
    private $_docker;

    /**
     * @var Compose
     */
    private $_compose;

    /**
     * @var Sync
     */
    private $_sync;

    /**
     * @var Framework
     */
    private $_framework;

    /**
     * @var ConsoleLogger
     */
    private $_logger;

    /**
     *  @var InputInterface
     */
    private $_input;

    /**
     * @param Compose $compose 
     * @param Sync $sync 
     * @param Filesystem $fs 
     * @param Finder $finder
     * @return null
     */
    public function __construct(
        SystemDocker $docker,
        Compose $compose,
        Sync $sync,
        Framework $framework
    ) {
        $this->_docker = $docker;
        $this->_compose = $compose;
        $this->_sync = $sync;
        $this->_framework = $framework;
    }



    public function start()
    {
        $this->logTitle('Starting dev environment...');

        $path = $this->_input->getOption('path');
        $build = $this->_input->getOption('build');

        $this->_sync->start($path);
        $this->_compose->up($path, $build);
    }

    public function stop()
    {
        $this->logTitle('Stopping dev environment...');

        $path = $this->_input->getOption('path');

        $this->_compose->stop($path);
    }

    public function nuke()
    {
        $this->logTitle('Nuking dev environment...');

        $path = $this->_input->getOption('path');

        $this->_compose->stop($path);
        $this->_compose->rm($path);
        $this->_sync->clean($path);
    }

    public function cleanup()
    {
        $this->logTitle('Cleaning up Docker leftovers...');

        $path = $this->_input->getOption('path');

        $this->_docker->cleanup($path);
    }

    public function ssh()
    {
        $this->logTitle('Connecting to server...');

        $path = $this->_input->getOption('path');
        $user = $this->_input->getOption('user');

        $this->logMessage("Connecting as $user");

        $this->_compose->ssh($path, $user);
    }

    public function cacheClear()
    {
        $commands = $this->_framework->clearCache();

        foreach ($commands as $command) {
            $this->runCommand($command);
        }
    }

    /**
     * Runs a command on the docker-compose php container
     * @param array $command 
     * @param bool $elevatePermissions 
     * @return null
     */
    public function runCommand(array $command = array(), $elevatePermissions = false)
    {
        $path = $this->_input->getOption('path');

        $command = array_merge(
            [
                'exec',
                '--user=' . ($elevatePermissions ? 'root' : 'www-data'),
                'php'
            ],
            $command
        );
        
        $this->_compose->runCmd(
            $path,
            $command
        );
    }


    /**
     * Returns docker compose system object
     * @return Compose
     */
    public function getCompose()
    {
        return $this->_compose;
    }

    /**
     * Returns docker sync system object
     * @return Sync
     */
    public function getSync()
    {
        return $this->_sync;
    }
}
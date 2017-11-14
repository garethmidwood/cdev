<?php

namespace Creode\Environment\Example;

use Creode\Cdev\Config;
use Creode\Environment\Environment;
use Creode\Framework\Framework;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;


class Example extends Environment
{
    const NAME = 'example';
    const LABEL = 'Example';
    const COMMAND_NAMESPACE = 'example';
    
    /**
     * @var Framework
     */
    protected $_framework;

    /**
     * @var Config
     */
    private $_config;

    /**
     * @param Framework $framework
     * @param Config $config
     * @return null
     */
    public function __construct(
        Framework $framework,
        Config $config
    ) {
        $this->_framework = $framework;
        $this->_config = $config;
    }

    public function start()
    {
        $this->logTitle('Starting dev environment...');
        $this->displayInstallationMessage();
    }

    public function stop()
    {
        $this->logTitle('Stopping dev environment...');
        $this->displayInstallationMessage();
    }

    public function nuke()
    {
        $this->logTitle('Nuking dev environment...');
        $this->displayInstallationMessage();
    }

    public function status()
    {
        $this->logTitle('Environment status');
        $this->displayInstallationMessage();
    }

    public function cleanup()
    {
        $this->logTitle('Cleaning up Docker leftovers...');
        $this->displayInstallationMessage();
    }

    public function ssh()
    {
        $this->logTitle('Connecting to server...');
        $this->displayInstallationMessage();
    }

    public function dbConnect()
    {
        $this->logTitle('Connecting to database...');
        $this->displayInstallationMessage();
    }

    public function runCommand(array $command = array(), $elevatePermissions = false) 
    {
        $this->logTitle('Running command...');
        $this->displayInstallationMessage();
    }

    public function displayInstallationMessage()
    {
        throw new \Exception('You have no environments installed. Try installing one, e.g. `cdev plugin:install cdev/environment-docker`'); 
    }
}

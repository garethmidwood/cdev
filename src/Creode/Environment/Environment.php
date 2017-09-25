<?php

namespace Creode\Environment;

use Creode\Tools\Logger;

abstract class Environment extends Logger
{
    /**
     * @var Framework
     */
    protected $_framework;
    
    abstract public function start();
    
    abstract public function stop();
    
    abstract public function nuke();

    abstract public function status();

    abstract public function cleanup();

    abstract public function ssh();

    abstract public function dbConnect();

    abstract public function runCommand(array $command = array(), $elevatePermissions = false);

    public function cacheClear()
    {
        $commands = $this->_framework->clearCache();

        foreach ($commands as $command) {
            $this->runCommand($command);
        }
    }

    public function update()
    {
        $commands = $this->_framework->update();

        foreach ($commands as $command) {
            $this->runCommand($command);
        }
    }
}

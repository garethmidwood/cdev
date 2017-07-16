<?php

namespace Creode\Environment;

use Creode\Tools\Logger;

abstract class Environment extends Logger
{
    public final function getCommandNamespace()
    {
        if (!defined(self::COMMAND_NAMESPACE)) {
            throw new LogicException(get_class($this) . ' must have a command namespace defined');
        }

        return $this->_cmdNamespace;
    }

    abstract public function setup(array $answers = array());
    
    abstract public function start();
    
    abstract public function stop();
    
    abstract public function nuke();

    abstract public function cleanup();

    abstract public function ssh();

    abstract public function runCommand(array $command = array(), $elevatePermissions = false);

    abstract public function cacheClear();
}

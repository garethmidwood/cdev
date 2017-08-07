<?php

namespace Creode\Environment;

use Creode\Tools\Logger;

abstract class Environment extends Logger
{
    abstract public function start();
    
    abstract public function stop();
    
    abstract public function nuke();

    abstract public function cleanup();

    abstract public function ssh();

    abstract public function dbConnect();

    abstract public function runCommand(array $command = array(), $elevatePermissions = false);

    abstract public function cacheClear();

    abstract public function update();
}

<?php

namespace Creode\Environment;

interface Environment
{
    public function setup(array $answers = array());
    
    public function start();
    
    public function stop();
    
    public function nuke();

    public function cleanup();

    public function ssh();

    public function runCommand(array $command = array(), $elevatePermissions = false);

    public function cacheClear();
}

<?php

namespace Creode\Environments;

interface Environment
{
    public function setup(array $answers = array());
    
    public function start();
    
    public function stop();
    
    public function nuke();

    public function cleanup();

    public function ssh();

    public function runCommand($cmd, array $options = array(), $elevatePermissions = false);
}

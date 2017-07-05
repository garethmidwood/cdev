<?php

namespace Creode\Tools;

use Symfony\Component\Console\Input\InputInterface;

interface ToolInterface
{
    public function setup(array $answers = array());
    
    public function start();
    
    public function stop();
    
    public function nuke();

    public function runCommand($cmd, array $options = array(), $elevatePermissions = false);
}

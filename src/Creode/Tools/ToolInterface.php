<?php

namespace Creode\Tools;

use Symfony\Component\Console\Input\InputInterface;

interface ToolInterface
{
    public function setup(InputInterface $input);
    public function start();
    public function stop();
    public function nuke();
}

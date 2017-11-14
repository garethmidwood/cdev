<?php

namespace Creode\Collections;

class EnvironmentCollection extends Collection
{
    public function __construct() 
    {
        \Creode\Cdev\Plugin\Manager::registerEnvironments($this);
    }
}

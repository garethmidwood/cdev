<?php

namespace Creode\Collections;

class EnvironmentCollection extends Collection
{
    // TODO: Find a better way to include these, ideally by injecting the classes (somehow)
    public function __construct() 
    {
        $this->addItem('\Creode\Environment\Docker\Docker');
    }
}

<?php

namespace Creode\Collections;

class FrameworkCollection extends Collection
{
    public function __construct()
    {
        $this->addItem('\Creode\Framework\Custom\Custom');

        \Creode\Cdev\Plugin\Manager::registerFrameworks($this);
    }
}

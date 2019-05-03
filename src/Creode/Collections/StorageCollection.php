<?php

namespace Creode\Collections;

class StorageCollection extends Collection
{
    public function __construct()
    {
        $this->addItem('\Creode\Storage\S3\S3');
        $this->addItem('\Creode\Storage\Server\Server');

        \Creode\Cdev\Plugin\Manager::registerStorage($this);
    }
}

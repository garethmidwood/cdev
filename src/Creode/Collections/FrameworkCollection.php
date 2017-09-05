<?php

namespace Creode\Collections;

class FrameworkCollection extends Collection
{
    // TODO: Find a better way to include these, ideally by injecting the classes (somehow)
    public function __construct() 
    {
        $this->addItem('\Creode\Framework\Magento1\Magento1');
        $this->addItem('\Creode\Framework\Magento2\Magento2');
        $this->addItem('\Creode\Framework\Drupal7\Drupal7');
        $this->addItem('\Creode\Framework\Drupal8\Drupal8');
        $this->addItem('\Creode\Framework\WordPress\WordPress');
    }
}

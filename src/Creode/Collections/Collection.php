<?php

namespace Creode\Collections;

abstract class Collection
{
    protected $items = array();

    public function getItems()
    {
        return $this->items;
    }

    public function addItem($item)
    {
        $this->items[] = $item;
    }
}

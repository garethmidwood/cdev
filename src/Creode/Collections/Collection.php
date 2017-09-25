<?php

namespace Creode\Collections;

abstract class Collection
{
    protected $items = array();

    public function getItems()
    {
        return $this->items;
    }

    protected function addItem($item)
    {
        $this->items[] = $item;
    }
}

<?php

namespace Creode\Framework;

abstract class Framework
{
    /**
     * Returns commands to clear cache on this framework
     * @return array
     */
    abstract public function clearCache() : array;

    /**
     * Returns commands to run updates on this framework
     * @return array
     */
    abstract public function update() : array;

    /**
     * Returns an array of tables that can have their data cleansed on dev environments
     * @return array
     */
    public function getDBTableCleanseList() : array
    {
        return [];
    }
}

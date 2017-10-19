<?php

namespace Creode\Framework;

abstract class Framework
{
    /**
     * Returns commands to clear cache on this framework
     * @return array
     */
    public function clearCache() {
        return [];
    }

    /**
     * Returns commands to run updates on this framework
     * @return array
     */
    public function update() {
        return [];
    }

    /**
     * Returns an array of tables that can have their data cleansed on dev environments
     * @return array
     */
    public function getDBTableCleanseList()
    {
        return [];
    }

    /**
     * returns commands to run start up functions on this framework
     * @return [type] [description]
     */
    public function startUp()
    {
        return [];
    }
}

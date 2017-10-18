<?php

namespace Creode\Framework\Laravel;

use Creode\Framework\Framework;

class Laravel extends Framework
{
    const NAME = 'Laravel';
    const LABEL = 'Laravel';

    const ARTISAN = 'artisan';

    /**
     * Runs inital artisan migration & seeds 
     * @return [array] [commands]
     */
    public function startUp() {
        return [
            ['php', self::ARTISAN, 'migrate'],
            ['php',self::ARTISAN, 'db:seed']
        ];
    }

    /**
     * Rebuilds the database & seeds
     * @return [array] [commands]
     */
    public function rebuild() {
        return [
            ['php', self::ARTISAN, 'migrate:refresh', '--seed'],
        ];
    }

    /**
     * Returns commands to clear cache on this framework
     * @return array
     */
    public function clearCache()
    {
        return [];
    }

    /**
     * Returns commands to run updates on this framework
     * @return array
     */
    public function update()
    {
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
}

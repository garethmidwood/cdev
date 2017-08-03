<?php

namespace Creode\Framework\Drupal7;

use Creode\Framework\Framework;

class Drupal7 implements Framework
{
    const NAME = 'drupal7';
    const LABEL = 'Drupal 7';

    const DRUSH = 'drush';

    /**
     * Returns commands to clear cache on this framework
     * @return array
     */
    public function clearCache() : array
    {
        return [
            // TODO: This will only clear the cache for one site
            [self::DRUSH, 'cc:all']
        ];
    }

    /**
     * Returns commands to run updates on this framework
     * @return array
     */
    public function update() : array
    {
        return [
            [self::DRUSH, 'updatedb']
        ];
    }
}

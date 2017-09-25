<?php

namespace Creode\Framework\Drupal8;

use Creode\Framework\Framework;

class Drupal8 extends Framework
{
    const NAME = 'drupal8';
    const LABEL = 'Drupal 8';

    const DRUSH = 'drush';

    /**
     * Returns commands to clear cache on this framework
     * @return array
     */
    public function clearCache()
    {
        return [
            // TODO: This will only clear the cache for one site
            [self::DRUSH, 'cr']
        ];
    }

    /**
     * Returns commands to run updates on this framework
     * @return array
     */
    public function update()
    {
        return [
            [self::DRUSH, 'updatedb']
        ];
    }
}

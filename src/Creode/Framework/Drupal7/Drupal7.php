<?php

namespace Creode\Framework\Drupal7;

use Creode\Framework\Framework;

class Drupal7 implements Framework
{
    const NAME = 'drupal7';

    const DRUSH = 'drush';

    /**
     * Returns commands to clear cache on this framework
     * @return array
     */
    public function clearCache() : array
    {
        return [
            [self::DRUSH, 'cc:all']
        ];
    }
}

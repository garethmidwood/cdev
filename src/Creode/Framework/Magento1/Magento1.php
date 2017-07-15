<?php

namespace Creode\Framework\Magento1;

use Creode\Framework\Framework;

class Magento1 implements Framework
{
    const NAME = 'magento1';

    const MAGERUN = 'bin/n98-magerun.phar';

    /**
     * Returns commands to clear cache on this framework
     * @return array
     */
    public function clearCache() : array
    {
        return [
            [self::MAGERUN, 'cache:clean'],
            [self::MAGERUN, 'cache:flush']
        ];
    }
}

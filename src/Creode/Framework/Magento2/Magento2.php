<?php

namespace Creode\Framework\Magento2;

use Creode\Framework\Framework;

class Magento2 extends Framework
{
    const NAME = 'magento2';
    const LABEL = 'Magento 2';

    const MAGERUN = 'bin/magento';

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

    /**
     * Returns commands to run updates on this framework
     * @return array
     */
    public function update() : array
    {
        return [
            [self::MAGERUN, 'setup:upgrade']
        ];
    }
}

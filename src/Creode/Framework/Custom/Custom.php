<?php

namespace Creode\Framework\Custom;

use Creode\Framework\Framework;

class Custom extends Framework
{
    const NAME = 'custom';
    const LABEL = 'Custom';

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
}

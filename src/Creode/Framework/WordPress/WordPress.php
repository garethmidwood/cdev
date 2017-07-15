<?php

namespace Creode\Framework\WordPress;

use Creode\Framework\Framework;

class WordPress implements Framework
{
    const NAME = 'wordpress';

    /**
     * Returns commands to clear cache on this framework
     * @return array
     */
    public function clearCache() : array
    {
        return [];
    }
}

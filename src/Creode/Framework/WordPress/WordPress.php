<?php

namespace Creode\Framework\WordPress;

use Creode\Framework\Framework;

class WordPress implements Framework
{
    const NAME = 'wordpress';
    const LABEL = 'WordPress';

    /**
     * Returns commands to clear cache on this framework
     * @return array
     */
    public function clearCache() : array
    {
        return [];
    }

    /**
     * Returns commands to run updates on this framework
     * @return array
     */
    public function update() : array
    {
        return [
            []
        ];
    }
}

<?php

namespace Creode\Framework;

interface Framework
{
    public function clearCache() : array;

    public function update() : array;
}

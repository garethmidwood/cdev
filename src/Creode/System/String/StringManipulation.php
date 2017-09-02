<?php

namespace Creode\System\String;

interface StringManipulation
{
    public function removeLinesMatching($path, $file, array $matches = [], $plainMatch = true);
}

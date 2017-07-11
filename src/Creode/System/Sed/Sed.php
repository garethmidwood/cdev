<?php
namespace Creode\System\Sed;

use Creode\System\Command;

// TODO: Move this to a 'str_rep' sub dir and create an interface. Use it in the CleanseCommand

class Sed extends Command
{
    const COMMAND = 'sed';

    public function removeLinesMatching($path, $file, array $matches = [], $plainMatch = true, $replaceFile = true) 
    {
        $terms = [];

        foreach ($matches as $match) {
            $terms[] = $plainMatch ? '/' . $match . '/d' : $match;
        }

        $termString = implode('; ', $terms);

        $args = $replaceFile ? ['-i'] : [];
        
        $this->run(
            self::COMMAND,
            array_merge($args, [$termString, $file]),
            $path
        );
    }
}

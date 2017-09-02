<?php
namespace Creode\System\String;

use Creode\System\Command;
use Symfony\Component\Filesystem\Filesystem;

class Awk extends Command implements StringManipulation
{
    const COMMAND = 'awk';

    /**
     * @var Filesystem
     */
    private $_fs;

    public function __construct(
        Filesystem $fs
    )
    {
        $this->_fs = $fs;
    }

    public function removeLinesMatching($path, $file, array $matches = [], $plainMatch = true) 
    {
        $terms = [];

        foreach ($matches as $match) {
            $terms[] = $plainMatch ? '!/' . $match . '/' : $match;
        }

        $termString = implode(' && ', $terms);

        $contents = $this->run(
            self::COMMAND,
            [
                $termString,
                $file
            ],
            $path,
            3600,
            false
        );

        $this->_fs->dumpFile(
            $file,
            $contents
        );
    }
}

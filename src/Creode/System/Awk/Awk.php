<?php
namespace Creode\System\Awk;

use Creode\System\Command;
use Symfony\Component\Filesystem\Filesystem;

// TODO: Move this to a 'string' sub dir and create an interface. Use it in the CleanseCommand

class Awk extends Command
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

    public function removeLinesMatching($path, $file, array $matches = [], $plainMatch = true, $replaceFile = true) 
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

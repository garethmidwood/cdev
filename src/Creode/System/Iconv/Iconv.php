<?php
namespace Creode\System\Iconv;

use Creode\System\Command;
use Symfony\Component\Filesystem\Filesystem;

class Iconv extends Command
{
    const COMMAND = 'iconv';

    /**
     * @var Filesystem
     */
    private $_fs;

    /**
     * @param Filesystem $fs 
     * @return null
     */
    public function __construct(
        Filesystem $fs
    )
    {
        $this->_fs = $fs;
    }

    /**
     * Converts a file to a new format
     * @param string $path 
     * @param string $file 
     * @param string $toFormat 
     * @param string $fromFormat 
     * @return null
     */
    public function convert($path, $file, $toFormat = 'utf-8', $fromFormat = 'ISO-8859-1') 
    {
        $contents = $this->run(
            self::COMMAND,
            [
                '-f',
                $fromFormat,
                '-t',
                $toFormat
            ],
            $path,
            3600,
            false,
            $file
        );

        $this->_fs->dumpFile(
            $file,
            $contents
        );
    }
}

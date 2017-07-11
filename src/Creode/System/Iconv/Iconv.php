<?php
namespace Creode\System\Iconv;

use Creode\System\Command;

class Iconv extends Command
{
    const COMMAND = 'iconv';

    public function convert($path, $file, $toFormat = 'utf-8', $fromFormat = 'ISO-8859-1') 
    {
        $this->run(
            self::COMMAND,
            [
                '-f',
                $fromFormat,
                '-t',
                $toFormat,
                '<',
                $file,
                '>',
                $file
            ],
            $path,
            3600,
            false
        );
    }
}

<?php
namespace Creode\Tools\Composer;

use Creode\Tools\Command;

class Composer extends Command
{
    public function init($path)
    {
        $this->run('cd ' . $path . ' && composer init');

        return 'composer init completed';
    }
}

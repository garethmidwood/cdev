<?php
namespace Creode\System\Docker;

use Creode\System\Command;

class Docker extends Command
{
    const COMMAND = 'docker';
    
    public function cleanup($path)
    {
        echo 'Removing containers' . PHP_EOL;
        $this->run(self::COMMAND, ['container', 'prune', '--force'], $path);
        echo 'Removing images' . PHP_EOL;
        $this->run(self::COMMAND, ['image', 'prune', '--force'], $path);
        echo 'Removing volumes' . PHP_EOL;
        $this->run(self::COMMAND, ['volume', 'prune', '--force'], $path);

        return 'Clean up complete.';
    }
}

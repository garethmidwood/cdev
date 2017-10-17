<?php
namespace Creode\Environment\Docker\System;

use Creode\System\Command;

class Docker extends Command
{
    const COMMAND = 'docker';
    
    public function cleanup($path)
    {
        $this->runCommand(self::COMMAND, ['container', 'prune', '--force'], $path);
        $this->runCommand(self::COMMAND, ['image', 'prune', '--force'], $path);
        $this->runCommand(self::COMMAND, ['volume', 'prune', '--force'], $path);

        return 'Clean up complete.';
    }

    public function pull($path, $image)
    {
        $this->runCommand(
            self::COMMAND,
            ['pull', $image],
            $path
        );
    }
}

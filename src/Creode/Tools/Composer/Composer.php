<?php
namespace Creode\Tools\Composer;

use Creode\Tools\SystemCommand;

class Composer extends SystemCommand
{
    const COMPOSER = '/usr/local/bin/composer.phar'; // TODO: Make this universal

    public function init($path, $packageName)
    {
        $this->run(
            self::COMPOSER,
            [
                'init',
                '-n',
                '--name', $packageName,
                '--require-dev', 'creode/docker:~1.0.0',
                '--stability', 'dev',
                '--repository', '{"type": "vcs", "url": "git@codebasehq.com:creode/creode/docker.git"}'
            ],
            $path
        );

        return 'composer init completed';
    }

    public function install($path)
    {
        $this->run(
            self::COMPOSER,
            [
                'install'
            ],
            $path
        );

        return 'composer install completed';
    }

}

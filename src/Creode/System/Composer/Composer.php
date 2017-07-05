<?php
namespace Creode\System\Composer;

use Creode\System\Command;

class Composer extends Command
{
    private $_composer;

    public function setPath($path)
    {
        $this->_composer = $path;
    }

    public function init($path, $packageName)
    {
        $this->run(
            $this->_composer,
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
            $this->_composer,
            [
                'install'
            ],
            $path
        );

        return 'composer install completed';
    }

}

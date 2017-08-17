<?php
namespace Creode\System\Git;

use Creode\System\Command;

class Git extends Command
{
    const COMMAND = 'git';

    public function init($path)
    {
        $this->run(
            self::COMMAND,
            [
                'init'
            ],
            $path
        );

        return 'git init completed';
    }

    public function getRepoURL($path) 
    {
        $mm = $this->run(
            self::COMMAND,
            [
                'remote',
                '-v'
            ],
            $path
        );

        $remotes = explode(PHP_EOL, $mm);
        foreach ($remotes as $remote) {
            $remoteData = preg_split('/\s+/', $remote);

            if ($remoteData[0] == 'origin') {
                return $remoteData[1];
            }
        }

        return false;
    }


    public function add($path, $files = '.')
    {
        $this->run(
            self::COMMAND,
            [
                'add',
                $files
            ],
            $path
        );

        return 'git add completed';
    }

    public function commit($path, $message)
    {
        $this->run(
            self::COMMAND,
            [
                'commit',
                '-m',
                $message
            ],
            $path
        );

        return 'git commit completed';
    }
}

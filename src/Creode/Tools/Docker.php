<?php

namespace Creode\Tools;

use Creode\Tools\Docker\Compose;
use Creode\Tools\Docker\Sync;

class Docker implements ToolInterface
{
    /**
     * @var Compose
     */
    private $_compose;

    /**
     * @var Sync
     */
    private $_sync;

    /**
     * @var ConsoleLogger
     */
    private $_logger;


    public function __construct(Compose $compose, Sync $sync)
    {
        $this->_compose = $compose;
        $this->_sync = $sync;
    }

    public function install()
    {
        return 'This bit hasn\'t been built yet';
        // - move code into /src directory
        //   - will need to ask if it's already in a sub dir
        // - run composer init (let it ask for its own inputs)
        // - run require creode/docker
        // - copy docker templates
        //   - ask for input on what the project name is (no spaces) and port number
    }

    public function start()
    {
        // make these log messages look nicer!
        echo 'starting...' . PHP_EOL;
        $this->_sync->start();
        $this->_compose->up();
    }

    public function stop()
    {
        $this->_compose->stop();
        $this->_sync->stop();
    }

    public function nuke()
    {
        $this->_compose->rm();
        $this->_sync->clean();
    }
}

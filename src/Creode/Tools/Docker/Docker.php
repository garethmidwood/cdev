<?php

namespace Creode\Tools\Docker;

use Creode\Tools\ToolInterface;
use Creode\Tools\Logger;
use Creode\Tools\Docker\Compose;
use Creode\Tools\Docker\Sync;
use Creode\Tools\Composer\Composer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputInterface;

class Docker extends Logger implements ToolInterface
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
     * @var Composer
     */
    private $_composer;

    /**
     * @var ConsoleLogger
     */
    private $_logger;

    /**
     * @var Filesystem
     */
    private $_fs;

    /**
     *  @var Finder
     */
    private $_finder;

    /**
     *  @var InputInterface
     */
    private $_input;

    /**
     * @param Compose $compose 
     * @param Sync $sync 
     * @param Filesystem $fs 
     * @param Finder $finder
     * @return null
     */
    public function __construct(
        Compose $compose,
        Sync $sync,
        Composer $composer,
        Filesystem $fs,
        Finder $finder
    ) {
        $this->_compose = $compose;
        $this->_sync = $sync;
        $this->_composer = $composer;
        $this->_fs = $fs;
        $this->_finder = $finder;
    }

    /**
     * Sets up the directory with docker 
     * @param InputInterface $input 
     * @return null
     */
    public function setup(InputInterface $input)
    {
        $this->_input = $input;

        $oldSrc = $this->_input->getOption('oldsrc');

        if (isset($oldSrc)) 
        {
            $this->renameSrcDir();
        } else {
            $this->createSrcDir();
            $this->moveFilesToSrc();
        }

        $this->composerInit();

        // - move code into /src directory
        //   - will need to ask if it's already in a sub dir
        // - run composer init (let it ask for its own inputs)
        // - run require creode/docker
        // - copy docker templates
        //   - ask for input on what the project name is (no spaces) and port number
    }

    public function start()
    {
        $this->logTitle('Starting dev environment...');

        $this->_sync->start();
        $this->_compose->up();
    }

    public function stop()
    {
        $this->logTitle('Stopping dev environment...');

        $this->_compose->stop();
        $this->_sync->stop();
    }

    public function nuke()
    {
        $this->logTitle('Nuking dev environment...');

        $this->_compose->rm();
        $this->_sync->clean();
    }


    private function renameSrcDir()
    {
        $this->logTitle('Renaming src directory');

        $path = $this->_input->getOption('path');
        $oldSrc = $this->_input->getOption('oldsrc');
        $src = $this->_input->getOption('src');

        $oldSrcPath = $path . '/' . $oldSrc;
        $srcPath = $path . '/' . $src;

        if (!$this->_fs->exists($oldSrcPath))
        {
            $this->logMessage("$oldSrc directory doesn't exist. Aborting");
            throw new \Exception("$oldSrc directory doesn't exist");
        }
            
        $this->logMessage("Renaming $oldSrc directory to $src");

        $this->_fs->rename(
            $oldSrcPath,
            $srcPath 
        );
    }

    private function createSrcDir()
    {
        $this->logTitle('Creating src directory');

        $path = $this->_input->getOption('path');
        $src = $this->_input->getOption('src');

        $srcPath = $path . '/' . $src;

        if ($this->_fs->exists($srcPath))
        {
            $this->logMessage("$src directory already exists. Continuing with existing dir");
            return;
        }
            
        $this->logMessage("Creating $src directory");
        
        $this->_fs->mkdir($srcPath, 0740);

        $this->logMessage("$src directory created");
    }

    private function moveFilesToSrc()
    {
        $this->logTitle('Moving files to src directory');

        $path = $this->_input->getOption('path');
        $src = $this->_input->getOption('src');

        $this->_finder
            ->in($path)
            ->depth('== 0')
            ->exclude($src)
            ->exclude('composer.*');

        foreach ($this->_finder as $file) {
            $this->logMessage("Moving {$file->getFileName()} into $src directory");

            $this->_fs->rename(
                $file->getPath() . '/' . $file->getFileName(),
                $file->getPath() . '/' . $src . '/' . $file->getFileName() 
            );
        }
    }

    private function composerInit()
    {
        $this->logTitle('Initialising composer');

        $path = $this->_input->getOption('path');

        $this->logMessage(
            $this->_composer->init($path)
        );
    }

}

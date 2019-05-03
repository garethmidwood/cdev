<?php

namespace Creode\Storage\Server;

use Creode\Cdev\Config;
use Creode\Storage\Storage;
use Creode\System\Ssh\Ssh;
use Symfony\Component\Console\Output\OutputInterface;

class Server extends Storage
{
    const NAME = 'server';
    const LABEL = 'server';
    const COMMAND_NAMESPACE = 'storage:server';

    /**
     * @var Config
     */
    private $_config;

    /**
     * @var Ssh
     */
    private $_ssh;

    /**
     * Constructor
     * @param Ssh $ssh 
     * @param Config $config 
     * @return void
     */
    public function __construct(
        Ssh $ssh,
        Config $config
    ) {
        $this->_config = $config;
        $this->_ssh = $ssh;
    }

    /**
     * Downloads the given file from the storage location
     * 
     * @param string $runPath The path to run the download command from
     * @param string $configNodeName The name of the node in the cdev config file that contains credentials etc.
     * @param string $source The location of the file to download
     * @param string $downloadLocation The location locally that the file should be downloaded to
     * @param OutputInterface $output The output interface, for returning status messages
     * @return bool
     */
    public function download(
        $runPath,
        $configNodeName,
        $source,
        $downloadLocation,
        OutputInterface $output
    ) {
        echo 'downloading from server...' . PHP_EOL;
    }
}

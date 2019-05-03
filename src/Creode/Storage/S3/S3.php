<?php

namespace Creode\Storage\S3;

use Creode\Cdev\Config;
use Creode\Storage\Storage;
use Symfony\Component\Console\Output\OutputInterface;

class S3 extends Storage
{
    const NAME = 's3';
    const LABEL = 's3';
    const COMMAND_NAMESPACE = 'storage:s3';

    /**
     * @var Config
     */
    private $_config;

    /**
     * Constructor
     * @param Config $config 
     * @return void
     */
    public function __construct(
        Config $config
    ) {
        $this->_config = $config;
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
        echo 'downloading from S3...' . PHP_EOL;
    }
}

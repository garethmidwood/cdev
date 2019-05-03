<?php

namespace Creode\Storage\S3;

use Creode\Cdev\Config;
use Creode\Storage\Storage;
use Creode\System\Aws\S3\S3 as AwsS3;
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
     * @var AwsS3
     */
    private $_s3;

    /**
     * Constructor
     * @param AwsS3 $s3
     * @param Config $config 
     * @return void
     */
    public function __construct(
        AwsS3 $s3,
        Config $config
    ) {
        $this->_s3 = $s3;
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
        $conf = $this->_config->get($configNodeName);

        return $this->_s3->download(
            $runPath,
            $conf['bucket'],
            $source,
            $downloadLocation,
            $output
        );
    }
}

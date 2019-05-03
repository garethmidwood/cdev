<?php

namespace Creode\Storage;

use Symfony\Component\Console\Output\OutputInterface;

abstract class Storage
{
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
    abstract public function download(
        $runPath,
        $configNodeName,
        $source,
        $downloadLocation,
        OutputInterface $output
    );
}

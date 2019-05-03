<?php
namespace Creode\System\Aws\S3;

use Creode\System\Aws\Aws;
use Symfony\Component\Console\Output\OutputInterface;

class S3 extends Aws
{
    const COMMAND_DOWNLOAD = 'aws s3 cp';

    /**
     * Downloads from S3 to a local dir
     * @param string $path 
     * @param string $bucket
     * @param string $srcPath 
     * @param string $targetPath 
     * @param OutputInterface $output 
     * @return void
     */
    public function download(
        $path,
        $bucket,
        $srcPath,
        $targetPath,
        OutputInterface $output
    ) {
        $downloadDir = dirname($targetPath);

        if (!file_exists($downloadDir)) {
            $output->writeln("Creating download directory $downloadDir");
            mkdir($downloadDir);
        }

        $this->runExternalCommand(
            self::COMMAND_DOWNLOAD,
            [
                's3://' . $bucket . '/' . $srcPath,
                $targetPath,
                '--recursive'
            ],
            $path
        );
    }

}

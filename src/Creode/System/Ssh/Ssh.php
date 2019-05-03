<?php
namespace Creode\System\Ssh;

use Creode\Cdev\Config;
use Creode\System\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

class Ssh extends Command
{
    const COMMAND_DOWNLOAD = 'scp';
    const COMMAND_CONNECT = 'ssh';

    /**
     * Downloads a file from the host
     * @param string $path Working directory
     * @param string $user The SSH username
     * @param string $host The SSH host
     * @param string $port The SSH port
     * @param string $srcPath The file to download
     * @param string $targetPath Where to store the downloaded file
     * @param OutputInterface $output
     * @return void
     */
    public function download(
        $path,
        $user,
        $host,
        $port,
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
                '-P' . $port,
                (strlen($user) > 0 ? $user . '@' : '') . $host . ':' . $srcPath,
                $targetPath
            ],
            $path
        );
    }

    /**
     * Opens an SSH connection
     * @param string $path Working directory
     * @param string $user The SSH username
     * @param string $host The SSH host
     * @param string $port The SSH port
     * @param OutputInterface $output 
     * @return void
     */
    private function connect(
        $path, 
        $user,
        $host,
        $port,
        OutputInterface $output
    ) {
        $this->runExternalCommand(
            self::COMMAND_CONNECT,
            [
                '-p' . $port,
                (strlen($user) > 0 ? $user . '@' : '') . $host
            ],
            $path
        );
    }
}

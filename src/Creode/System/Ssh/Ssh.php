<?php
namespace Creode\System\Ssh;

use Creode\Cdev\Config;
use Creode\System\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

class Ssh extends Command
{
    const CONFIG_GROUP = 'backups';
    const COMMAND_DOWNLOAD = 'scp';
    const COMMAND_CONNECT = 'ssh';

    /**
     * @var Config
     */
    private $_config;

    /**
     * @param Config $config 
     * @return null
     */
    public function __construct(
        Config $config
    ) {
        $this->_config = $config;
    }

    /**
     * Downloads a file from the host
     * @param string $path Working directory
     * @param string $configNode The name of the server details node in the config yml file
     * @param string $srcPath The file to download
     * @param string $targetPath Where to store the downloaded file
     * @param OutputInterface $output
     * @return string
     */
    public function download($path, $configNode, $srcPath, $targetPath, OutputInterface $output)
    {
        $downloadDir = dirname($targetPath);

        if (!file_exists($downloadDir)) {
            $output->writeln("Creating download directory $downloadDir");
            mkdir($downloadDir);
        }

        $conf = $this->_config->get($configNode);

        $this->runExternalCommand(
            self::COMMAND_DOWNLOAD,
            [
                '-P' . $conf['port'],
                $conf['user'] . '@' . $conf['host'] . ':' . $srcPath,
                $targetPath
            ],
            $path
        );
    }

    private function connect($path, $configNode, OutputInterface $output)
    {
        $conf = $this->_config->get($configNode);

        $this->runExternalCommand(
            self::COMMAND_CONNECT,
            [
                '-p' . $conf['port'],
                $conf['user'] . '@' . $conf['host']
            ],
            $path
        );
    }
}

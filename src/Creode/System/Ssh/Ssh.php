<?php
namespace Creode\System\Ssh;

use Creode\Cdev\Config;
use Creode\System\Command;
use Creode\System\Ssh\Factory;
use Symfony\Component\Console\Output\OutputInterface;
use hexpang\Client\SSHClient\SSHClient;
use Psr\Log\LoggerInterface;

class Ssh extends Command
{
    const CONFIG_GROUP = 'backups';

    /**
     * @var Config
     */
    private $_config;

    /**
     * @var SSHClient
     */
    private $_sshClient;

    /**
     * @param Filesystem $fs 
     * @return null
     */
    public function __construct(
        Factory $sshFactory,
        Config $config
    ) {
        $this->_config = $config;
        $this->_sshFactory = $sshFactory;
    }

    /**
     * Downloads a file from the host
     * @param string $configNode The name of the server details node in the config yml file
     * @param string $srcPath The file to download
     * @param string $targetPath Where to store the downloaded file
     * @param OutputInterface $output
     * @return string
     */
    public function download($configNode, $srcPath, $targetPath, OutputInterface $output)
    {
        $downloadDir = dirname($targetPath);

        if (!file_exists($downloadDir)) {
            $output->writeln("Creating download directory $downloadDir");
            mkdir($downloadDir);
        }

        $conf = $this->_config->get($configNode);

        $sshClient = $this->_sshFactory::create(
            $conf['host'],
            $conf['port'],
            $conf['user'],
            $conf['pass']
        );

        $output->writeln("Connecting to server");
        $this->connect(
            $sshClient,
            $conf['host'],
            $conf['port']
        );

        $output->writeln("Downloading $srcPath");
        $sshClient->scp_recv($srcPath, $targetPath);
        
        $output->writeln("Disconnecting from server");
        $sshClient->disconnect();

        return 'Download complete';
    }

    private function connect(SSHClient $sshClient, $host, $port)
    {
        if ($sshClient->ping($host, $port, 10)) {

            if (!$sshClient->connect() || !$sshClient->authorize()) {
                throw new \Exception("Could not authorize connection to $host");
            }  

            return true;

        } else {
            throw new \Exception("Timed out when pinging $host");
        }

        return false;
    }
}

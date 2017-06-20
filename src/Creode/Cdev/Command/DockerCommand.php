<?php
namespace Creode\Cdev\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Creode\Tools\Docker as Docker;

class DockerCommand extends Command
{
    /**
     * @var Docker
     */
    private $_docker;

    /**
     * Constructor
     * @param Docker $docker 
     * @return null
     */
    public function __construct(Docker $docker)
    {
        $this->_docker = $docker;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('docker');
        $this->setDescription('Run Docker Commands');

        $this->addArgument('whatdoyouwannado', InputArgument::REQUIRED, 'Command to run: install, start, stop or nuke');

        // $this->addOption('path', 'p', InputOption::VALUE_REQUIRED, '', getcwd());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $input->getArgument('whatdoyouwannado');

        if (!method_exists($this->_docker, $command)) 
        {
            $output->writeln("Command '$command' doesn't exist");
            return;
        }

        $output->writeln(
            $this->_docker->$command()
        );
    }
}

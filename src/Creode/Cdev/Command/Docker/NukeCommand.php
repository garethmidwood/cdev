<?php
namespace Creode\Cdev\Command\Docker;

use Creode\Cdev\Command\ToolCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NukeCommand extends ToolCommand
{
    protected function configure()
    {
        $this->setName('docker:nuke');
        $this->setDescription('Destroys the project virtual environment');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_tool->input($input);
        
        $output->writeln(
            $this->_tool->nuke()
        );
    }
}

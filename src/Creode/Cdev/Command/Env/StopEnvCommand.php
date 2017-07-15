<?php
namespace Creode\Cdev\Command\Env;

use Creode\Cdev\Command\Env\EnvCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopEnvCommand extends EnvCommand
{
    protected function configure()
    {
        $this->setName('env:stop');
        $this->setDescription('Stops the virtual environment');

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
        $this->_environment->input($input);
        
        $output->writeln(
            $this->_environment->stop()
        );
    }
}

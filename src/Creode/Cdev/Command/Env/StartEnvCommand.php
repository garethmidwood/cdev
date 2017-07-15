<?php
namespace Creode\Cdev\Command\Env;

use Creode\Cdev\Command\Env\EnvCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartEnvCommand extends EnvCommand
{
    protected function configure()
    {
        $this->setName('env:start');
        $this->setDescription('Starts the project virtual environment');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );

        // TODO: This is tool-specific. Find a way to make it so.
        $this->addOption(
            'build',
            'b',
            InputOption::VALUE_NONE,
            'Rebuilds the environment'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_environment->input($input);

        $output->writeln(
            $this->_environment->start()
        );
    }
}

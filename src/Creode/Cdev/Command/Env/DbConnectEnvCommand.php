<?php
namespace Creode\Cdev\Command\Env;

use Creode\Cdev\Command\Env\EnvCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbConnectEnvCommand extends EnvCommand
{
    protected function configure()
    {
        $this->setName('env:db:connect');
        $this->setDescription('Connects to database container');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );

        $this->addOption(
            'database',
            'd',
            InputOption::VALUE_REQUIRED,
            'The database to connect to',
            'website'
        );

        $this->addOption(
            'user',
            'u',
            InputOption::VALUE_REQUIRED,
            'The user to connect as',
            'webuser'
        );

        $this->addOption(
            'password',
            'w',
            InputOption::VALUE_REQUIRED,
            'The user password',
            'webpassword'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_environment->input($input);
        
        $output->writeln(
            $this->_environment->dbConnect()
        );
    }
}

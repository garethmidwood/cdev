<?php
namespace Creode\Cdev\Command\Env;

use Creode\Cdev\Command\Env\EnvCommand;
use Creode\Cdev\Config;
use Creode\Environment\Environment;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SetupEnvCommand extends EnvCommand
{
    /**
     * @var Config
     */
    private $_config;

    /**
     * @param Config $config 
     * @return null
     */
    public function __construct(
        Environment $environment,
        Config $config
    ) {
        $this->_config = $config;

        parent::__construct($environment);
    }

    protected function configure()
    {
        $this->setName('env:setup');
        $this->setDescription('Sets up the project environment');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );

        $this->addOption(
            'composer',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Path to composer executable',
            '/usr/local/bin/composer.phar'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('docker:setup');

        $arguments = array(
            'command' => 'docker:setup'
        );

        $cmdInput = new ArrayInput($arguments);

        return $command->run($cmdInput, $output);
    }
}

<?php

namespace Creode\Cdev\Command\Plugin;

use Creode\Cdev\Command\ConfigurationCommand;
use Creode\System\Composer\Composer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddRepositoryCommand extends ConfigurationCommand
{
    /**
     * @var Composer
     */
    private $_composer;

    /**
     * Constructor
     * @param Composer $composer 
     * @return void
     */
    public function __construct(
        Composer $composer
    ) {
        $this->_composer = $composer;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('plugin:add-repository');
        $this->setDescription('Adds a new composer repository to pull a plugin from a source other than packagist.');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );

        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the repository to add. This is usually found in the composer file for the item.');
        $this->addArgument('type', InputArgument::REQUIRED, 'The type of the repository to add e.g. vcs, git etc.');
        $this->addArgument('url', InputArgument::REQUIRED, 'The url of the repository to add');
    }

    /**
     * Executes the command
     * @param InputInterface $input 
     * @param OutputInterface $output 
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_input = $input;
        $this->_output = $output;

        $this->addRepository();
    }

    /**
     * Adds repository to composer via cdev
     * @return void
     */
    private function addRepository()
    {
        $path = \Creode\Cdev\Plugin\Manager::getPluginDir();

        $command_options = $this->getCommandOptions();

        $this->_composer->config($path, $command_options);
    }

    /**
     * Functionality to get the main options for this command.
     *
     * @return array
     *    Array of options to run as a command.
     */
    private function getCommandOptions() {
        $command_options = [];
        
        $type = $this->_input->getArgument('type');
        $url = $this->_input->getArgument('url');
        $name = $this->_input->getArgument('name');

        $command_options[] = 'repositories.' . $name;
        $command_options[] = $type;
        $command_options[] = $url;

        return $command_options;
    }
}

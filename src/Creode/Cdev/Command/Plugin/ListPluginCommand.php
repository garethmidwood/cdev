<?php
namespace Creode\Cdev\Command\Plugin;

use Creode\Cdev\Command\ConfigurationCommand;
use Creode\System\Composer\Composer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListPluginCommand extends ConfigurationCommand
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
        $this->setName('plugin:list');
        $this->setDescription('Lists installed cdev plugins');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );
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

        $this->listPlugins();
    }

    /**
     * Lists the installed plugins
     * @param string|null $package 
     * @return type
     */
    private function listPlugins($package = null)
    {
        $path = \Creode\Cdev\Plugin\Manager::getPluginDir();

        $this->_composer->info($path);
    }

}

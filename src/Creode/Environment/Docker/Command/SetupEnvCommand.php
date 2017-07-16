<?php
namespace Creode\Environment\Docker\Command;

use Creode\Cdev\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class SetupEnvCommand extends Command
{
    /**
     * @var Filesystem
     */
    protected $_fs;

    /**
     * @var Finder
     */
    protected $_finder;

    /**
     * Constructor
     * @param Filesystem $fs
     * @param Finder $finder
     * @return null
     */
    public function __construct(
        Filesystem $fs,
        Finder $finder
    ) {
        $this->_fs = $fs;
        $this->_finder = $finder;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('docker:setup');
        $this->setHidden(true);
        $this->setDescription('Sets up the docker environment config');

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
        $output->writeln('Triggered docker setup');
        return;

        $path = $this->_input->getOption('path');
        $configDir = $path . '/' . Config::CONFIG_DIR;
        $configFile = $configDir . Config::CONFIG_FILE;
        $servicesFile = $configDir . Config::SERVICES_FILE;

        if (file_exists($configFile)) {
            $this->_config = Yaml::parse(file_get_contents($configFile));
        }



        $answers = $this->askQuestions($input, $output);

        $this->_environment->input($input);

        $output->writeln(
            $this->_environment->setup($answers)
        );
    }

    private function askQuestions(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // TODO: This is tool-specific. Find a way to make it so.

        $question = new Question('Package name (<vendor>/<name>) ', 'creode/toolazytotype');
        $answers['packageName'] = $helper->ask($input, $output, $question);

        $question = new Question('Environment port suffix (3 digits - e.g. 014) ', 'XXX');
        $answers['portNo'] = $helper->ask($input, $output, $question);

        $question = new Question('Project name (xxxx).docker ', 'toolazytotype');
        $answers['projectName'] = $helper->ask($input, $output, $question);

        return $answers;
    }
}

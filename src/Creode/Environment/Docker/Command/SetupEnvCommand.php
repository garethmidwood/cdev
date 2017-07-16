<?php
namespace Creode\Environment\Docker\Command;

use Creode\Cdev\Command\ConfigurationCommand;
use Creode\Cdev\Config;
use Creode\Environment\Docker\Docker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class SetupEnvCommand extends ConfigurationCommand
{
    protected $_config = array(
        'version' => '2',
        'config' => array(
            'docker' => array(
                'src' => null
            ),
            'compose' => array(
                'type' => null,
            ),
            'sync' => array(
                'active' => false,
            )
        )
    );

    /**
     * @var Docker
     */
    protected $_docker;

    /**
     * Constructor
     * @param Docker $docker
     * @return null
     */
    public function __construct(
        Docker $docker
    ) {
        $this->_docker = $docker;

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
        $this->_input = $input;
        $this->_output = $output;

        $path = $this->_input->getOption('path');

        $this->loadConfig($path, $output);

        $this->askQuestions();

        $this->saveConfig($path);
        $this->_docker->getCompose()->generateConfig();
        $this->_docker->getSync()->generateConfig();
    }

    private function askQuestions()
    {
        $helper = $this->getHelper('question');

        $default = isset($this->_config['config']['docker']['name']) ? $this->_config['config']['docker']['name'] : null;
        $question = new Question('Project name/domain (xxxx).docker: ', $default);
        $question->setValidator(function ($answer) {
            if (!filter_var('http://'.$answer.'.com', FILTER_VALIDATE_URL)) {
                throw new \RuntimeException(
                    'Docker project name must be suitable for use in domain name (no spaces, underscores etc.)'
                );
            }

            return $answer;
        });
        $this->_config['config']['docker']['name'] = $helper->ask($this->_input, $this->_output, $question);



        $default = isset($this->_config['config']['docker']['package']) ? $this->_config['config']['docker']['package'] : null;
        $question = new Question('Composer package name (<vendor>/<name>): ', $default);
        $this->_config['config']['docker']['package'] = $helper->ask($this->_input, $this->_output, $question);


        $default = isset($this->_config['config']['docker']['port']) ? $this->_config['config']['docker']['port'] : null;
        $question = new Question('Environment port suffix (3 digits - e.g. 014): ', $default);
        $question->setValidator(function ($answer) {
            if (!preg_match('/^[0-9]{3}$/', $answer)) {
                throw new \RuntimeException(
                    'Docker port number must be a 3 digit number'
                );
            }

            return $answer;
        });
        $this->_config['config']['docker']['port'] = $helper->ask($this->_input, $this->_output, $question);
    }
}

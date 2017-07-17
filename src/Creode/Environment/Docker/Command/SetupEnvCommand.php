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
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class SetupEnvCommand extends ConfigurationCommand
{
    protected $_config = [
        'config' => [
            'docker' => [
                'name' => null,
                'package' => null,
                'port' => null,
                'sync' => [
                    'active' => false,
                    'exclusions' => [
                        '.sass-cache',
                        'sass',
                        'sass-cache',
                        'bower.json',
                        'package.json',
                        'Gruntfile',
                        'bower_components',
                        'node_modules',
                        '.gitignore',
                        '.git',
                        '*.scss',
                        '*.sass'
                    ]
                ],
                'compose' => [
                    'services' => [
                        'mysql'=> [
                            'active' => true
                        ],
                        'php' => [
                            'active' => true
                        ],
                        'mailcatcher' => [
                            'active' => true
                        ],
                        'redis' => [
                            'active' => true
                        ]
                    ]
                ]
            ]
        ]
    ];
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

        $default = $this->_config['config']['docker']['name'];
        $question = new Question(
            '<question>Project name/domain (xxxx).docker</question> : [Current: <info>' . (isset($default) ? $default : 'None') . '</info>]',
            $default
        );
        $question->setValidator(function ($answer) {
            if (!filter_var('http://'.$answer.'.com', FILTER_VALIDATE_URL)) {
                throw new \RuntimeException(
                    'Docker project name must be suitable for use in domain name (no spaces, underscores etc.)'
                );
            }

            return $answer;
        });
        $this->_config['config']['docker']['name'] = $helper->ask($this->_input, $this->_output, $question);



        $default = $this->_config['config']['docker']['package'];
        $question = new Question(
            '<question>Composer package name (<vendor>/<name>)</question> : [Current: <info>' . (isset($default) ? $default : 'None') . '</info>]',
            $default
        );
        $question->setValidator(function ($answer) {
            if (!preg_match('/^[A-Za-z0-9]+\/[-A-Za-z0-9]+$/', $answer)) {
                throw new \RuntimeException(
                    'Package name must be in the format <vendor>/<name> e.g. creode/magento-1'
                );
            }

            return $answer;
        });
        $this->_config['config']['docker']['package'] = $helper->ask($this->_input, $this->_output, $question);



        $default = $this->_config['config']['docker']['port'];
        $question = new Question(
            '<question>Environment port suffix (3 digits - e.g. 014)</question> : [Current: <info>' . (isset($default) ? $default : 'None') . '</info>]',
            $default
        );
        $question->setValidator(function ($answer) {
            if (!preg_match('/^[0-9]{3}$/', $answer)) {
                throw new \RuntimeException(
                    'Docker port number must be a 3 digit number'
                );
            }

            return $answer;
        });
        $this->_config['config']['docker']['port'] = $helper->ask($this->_input, $this->_output, $question);


        $default = $this->_config['config']['docker']['sync']['active'];
        $optionsLabel = $default ? 'Y/n' : 'y/N';
        $question = new ConfirmationQuestion(
            '<question>Use docker-sync? ' . $optionsLabel . '</question> : [Current: <info>' . ($default ? 'Yes' : 'No') . '</info>]',
            $default,
            '/^(y|j)/i'
        );
        $this->_config['config']['docker']['sync']['active'] = $helper->ask($this->_input, $this->_output, $question);

        if ($this->_config['config']['docker']['sync']['active']) {
            $this->askDockerSyncQuestions();
        }

        $this->askDockerComposeQuestions();
    }

    /**
     * Asks questions to setup docker sync
     * @return null
     */
    private function askDockerSyncQuestions()
    {
        $helper = $this->getHelper('question');

        $default = implode(', ', $this->_config['config']['docker']['sync']['exclusions']);
        $question = new Question(
            '<question>Exclusions</question> : [Current: <info>' . $default . '</info>]',
            $default
        );
        $question->setValidator(function ($answer) {
            if (!preg_match('/^[-\w\s*-_.]+(?:,[-\w\s*-_.]*)*$/', $answer)) {
                throw new \RuntimeException(
                    'Exclusions must be a comma separated list'
                );
            }

            return $answer;
        });

        $answer = $helper->ask($this->_input, $this->_output, $question);
        $answer = str_replace(' ', '', $answer);

        $this->_config['config']['docker']['sync']['exclusions'] = explode(',', $answer);
    }

    /**
     * Asks questions to setup docker compose
     * @return null
     */
    private function askDockerComposeQuestions()
    {
        $helper = $this->getHelper('question');

        $useMysql = $this->containerRequired(
            'MySQL',
            $this->_config['config']['docker']['compose']['services']['mysql']['active']
        );

        $useMysql = $this->containerRequired(
            'php',
            $this->_config['config']['docker']['compose']['services']['php']['active']
        );

        $useMysql = $this->containerRequired(
            'Mailcatcher',
            $this->_config['config']['docker']['compose']['services']['mailcatcher']['active']
        );

        $useMysql = $this->containerRequired(
            'Redis',
            $this->_config['config']['docker']['compose']['services']['redis']['active']
        );
    }


    private function containerRequired($label, &$config) 
    {
        $helper = $this->getHelper('question');
        $optionsLabel = $config ? 'Y/n' : 'y/N';
        $question = new ConfirmationQuestion(
            '<question>' . $label . '? ' . $optionsLabel . '</question> : [Current: <info>' . ($config ? 'Yes' : 'No') . '</info>]',
            $config,
            '/^(y|j)/i'
        );
        $config = $helper->ask($this->_input, $this->_output, $question);
    }
}

<?php
namespace Creode\Environment\Docker\Command;

use Creode\Cdev\Command\ConfigurationCommand;
use Creode\Cdev\Config;
use Creode\Environment\Docker\Docker;
use Creode\Environment\Docker\System\Compose\Compose;
use Creode\Environment\Docker\System\Sync\Sync;
use Creode\System\Composer\Composer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
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
                    'version' => '2',
                    'services' => [
                        'mysql'=> [
                            'active' => true,
                            'container_name' => 'project_mysql',
                            'restart' => 'always',
                            'ports' => [
                                '3306:3306'
                            ],
                            'environment' => [
                                'MYSQL_ROOT_PASSWORD' => 'root',
                                'MYSQL_DATABASE' => 'website',
                                'MYSQL_USER' => 'webuser',
                                'MYSQL_PASSWORD' => 'webpassword'
                            ],
                            'volumes' => [
                                '../db:/docker-entrypoint-initdb.d',
                                '/var/lib/mysql',
                            ]
                        ],
                        'php' => [
                            'active' => true,
                            'container_name' => 'project_php',
                            'ports' => [
                                '80:80'
                            ],
                            'environment' => [
                                'VIRTUAL_HOST' => '.project.docker'
                            ],
                            'links' => [
                                'mysql:mysql',
                                'mailcatcher:mailcatcher',
                                'redis'
                            ],
                            'volumes' => [
                                ['../src:/var/www/html']
                            ]
                        ],
                        'mailcatcher' => [
                            'active' => true,
                            'container_name' => 'project_mailcatcher',
                            'ports' => [
                                '1080:1080'
                            ]
                        ],
                        'redis' => [
                            'active' => true,
                            'container_name' => 'project_redis',
                            'ports' => [
                                '6379'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    /**
     * @var boolean
     */
    private $_usingLocalBuilds = false;

    /**
     * @var Docker
     */
    protected $_docker;

    /**
     * @var Composer
     */
    protected $_composer;

    /**
     * @var Filesystem
     */
    protected $_fs;

    /**
     * Constructor
     * @param Docker $docker
     * @param Composer $composer
     * @return null
     */
    public function __construct(
        Docker $docker,
        Composer $composer,
        Filesystem $fs
    ) {
        $this->_docker = $docker;
        $this->_composer = $composer;
        $this->_fs = $fs;

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

        $this->saveDockerComposeConfig();

        if ($this->_config['config']['docker']['sync']['active']) {
            $this->saveDockerSyncConfig();
        }

        if ($this->_usingLocalBuilds) {
            $this->composerInit();
        }
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

        /**
         * 
         *  MySQL 
         *
         */
        $useMysql = $this->containerRequired(
            'MySQL',
            $this->_config['config']['docker']['compose']['services']['mysql']['active']
        );

        if ($useMysql) {
            $this->buildOrImage(
                '../vendor/creode/docker/images/mysql',
                'creode/mysql:5.6',
                $this->_config['config']['docker']['compose']['services']['mysql'],
                [   // builds
                    '../vendor/creode/docker/images/mysql' => 'MySQL'
                ],
                [   // images
                    'creode/mysql:5.6' => 'MySQL'
                ]
            );

            $this->_config['config']['docker']['compose']['services']['mysql']['container_name']
                = $this->_config['config']['docker']['name'] . '_mysql';

            $this->_config['config']['docker']['compose']['services']['mysql']['ports']
                = ['4' . $this->_config['config']['docker']['port'] . ':3306'];
        }

        /**
         * 
         *  PHP
         *
         */
        $usePhp = $this->containerRequired(
            'php',
            $this->_config['config']['docker']['compose']['services']['php']['active']
        );

        if ($usePhp) {
            $this->buildOrImage(
                '../vendor/creode/docker/images/php/7.0',
                'creode/php-apache:7.0',
                $this->_config['config']['docker']['compose']['services']['php'],
                [   // builds
                    '../vendor/creode/docker/images/php/7.0' => 'PHP 7.0',
                    '../vendor/creode/docker/images/php/5.6' => 'PHP 5.6',
                    '../vendor/creode/docker/images/php/5.3' => 'PHP 5.3'
                ],
                [   // images
                    'creode/php-apache:7.0' => 'PHP 7.0',
                    'creode/php-apache:5.6' => 'PHP 5.6',
                    'creode/php-apache:5.3' => 'PHP 5.3'
                ]
            );

            $this->_config['config']['docker']['compose']['services']['php']['container_name']
                = $this->_config['config']['docker']['name'] . '_php';

            $this->_config['config']['docker']['compose']['services']['php']['ports']
                = ['3' . $this->_config['config']['docker']['port'] . ':80'];

            $this->_config['config']['docker']['compose']['services']['php']['environment']['VIRTUAL_HOST']
                = '.' . $this->_config['config']['docker']['name'] . '.docker';


            $this->_config['config']['docker']['compose']['services']['php']['links']
                = []; 

            $this->_config['config']['docker']['compose']['services']['php']['volumes']
                = ['../' . $this->_config['config']['dir']['src'] . ':/var/www/html'];
        }

        /**
         * 
         *  Mailcatcher 
         *
         */
        $useMailcatcher = $this->containerRequired(
            'Mailcatcher',
            $this->_config['config']['docker']['compose']['services']['mailcatcher']['active']
        );

        if ($useMailcatcher) {
            $this->_config['config']['docker']['compose']['services']['mailcatcher']['image'] 
                = 'schickling/mailcatcher';

            $this->_config['config']['docker']['compose']['services']['mailcatcher']['container_name']
                = $this->_config['config']['docker']['name'] . '_mailcatcher';

            $this->_config['config']['docker']['compose']['services']['mailcatcher']['ports']
                = ['5' . $this->_config['config']['docker']['port'] . ':1080'];
        }

        /**
         * 
         *  Redis
         *
         */
        $useRedis = $this->containerRequired(
            'Redis',
            $this->_config['config']['docker']['compose']['services']['redis']['active']
        );

        if ($useRedis) {
            $this->_config['config']['docker']['compose']['services']['redis']['image'] 
                = 'redis';

            $this->_config['config']['docker']['compose']['services']['redis']['container_name']
                = $this->_config['config']['docker']['name'] . '_redis';

            $this->_config['config']['docker']['compose']['services']['redis']['ports']
                = ['6379'];
        }


        // sort out PHP links
        if ($usePhp) {
            if ($useMysql) {
                $this->_config['config']['docker']['compose']['services']['php']['links'][] = 'mysql:mysql';
            }
            if ($useMailcatcher) {
                $this->_config['config']['docker']['compose']['services']['php']['links'][] = 'mailcatcher:mailcatcher';
            }
            if ($useRedis) {
                $this->_config['config']['docker']['compose']['services']['php']['links'][] = 'redis';
            }

            if (empty($this->_config['config']['docker']['compose']['services']['php']['links'])) {
                unset($this->_config['config']['docker']['compose']['services']['php']['links']);
            }
        }

        $drupals = [
            \Creode\Framework\Drupal8\Drupal8::NAME,
            \Creode\Framework\Drupal7\Drupal7::NAME
        ];

        if (in_array($this->_config['config']['environment']['framework'], $drupals)) {
            $this->askDrupalQuestions();
        }
    }

    private function askDrupalQuestions() 
    {
        if (
            !$this->_config['config']['docker']['compose']['services']['php']['active'] ||
            !$this->_config['config']['docker']['compose']['services']['mysql']['active']
        ) {
            $this->_output->writeln('<info>Skipping Drush setup as php or mysql is not active</info>');
            $this->_config['config']['docker']['compose']['services']['drush']['active'] = false;
        }

        /**
         * 
         *  Drush
         *
         */
        $useDrush = $this->containerRequired(
            'Drush',
            $this->_config['config']['docker']['compose']['services']['drush']['active']
        );

        if ($useDrush) {
            $this->_config['config']['docker']['compose']['services']['drush']['image'] 
                = 'drupaldocker/drush';

            $this->_config['config']['docker']['compose']['services']['drush']['links']
                = ['mysql'];

            $this->_config['config']['docker']['compose']['services']['drush']['volumes_from']
                = ['php'];
        }
    }

    /**
     * Asks whether a container is required and saves results
     * @param string $label 
     * @param string|array &$config 
     * @return boolean
     */
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

        return $config;
    }

    /**
     * Asks whether to use an image or build the image from local scripts
     * @param string $defaultBuild 
     * @param string $defaultImage 
     * @param array &$config
     * @param array $builds
     * @param array $images
     */
    private function buildOrImage(
        $defaultBuild,
        $defaultImage,
        array &$config,
        array $builds = [],
        array $images = []
    )
    {
        $helper = $this->getHelper('question');

        $current = isset($config['build']) ? 'build' : (isset($config['image']) ? 'image' : null);
        $default = isset($current) ? $current : 'image';        

        $question = new ChoiceQuestion(
            '<question>Build or Image:</question> [Current: <info>' . $default . '</info>]',
            [
                'build' => 'build',
                'image' => 'image'
            ],
            $default
        );
        $question->setErrorMessage('Choice %s is invalid.');
        $chosen = $helper->ask($this->_input, $this->_output, $question);

        switch($chosen) {
            case 'build':
                $this->_usingLocalBuilds = true;

                if (isset($config['image'])) {
                    unset($config['image']);
                }

                $default = isset($config['build']) ? $config['build'] : $defaultBuild;

                $question = new ChoiceQuestion(
                    '<question>Build:</question> [Current: <info>' . $default . '</info>]',
                    $builds,
                    $default
                );
                $question->setErrorMessage('Build %s is invalid.');
                $config['build'] = $helper->ask($this->_input, $this->_output, $question);
                break;
            case 'image':
                if (isset($config['build'])) {
                    unset($config['build']);
                }

                $default = isset($config['image']) ? $config['image'] : $defaultImage;

                $question = new ChoiceQuestion(
                    '<question>Image:</question> [Current: <info>' . $default . '</info>]',
                    $images,
                    $default
                );
                $question->setErrorMessage('Image %s is invalid.');
                $config['image'] = $helper->ask($this->_input, $this->_output, $question);
                break;
        }
    }

    /**
     * Saves the docker compose config file
     * @return null
     */
    private function saveDockerComposeConfig()
    {
        $path = $this->_input->getOption('path');

        $config = $this->_config['config']['docker']['compose'];

        $activeServices = $config;

        foreach ($config['services'] as $key => &$service) {
            if ($service['active']) {
                unset($activeServices['services'][$key]['active']);
            } else {
                unset($activeServices['services'][$key]);
            }
        }

        $this->saveConfig(
            $path,
            Config::CONFIG_DIR, 
            Compose::FILE,
            $activeServices
        );
    }

    /**
     * Saves the docker sync config file
     * @return null
     */
    private function saveDockerSyncConfig()
    {
        $path = $this->_input->getOption('path');
        
        $config = $this->_config['config']['docker']['sync'];
        unset($config['active']);

        $this->saveConfig(
            $path,
            Config::CONFIG_DIR, 
            Sync::FILE,
            $config
        );
    }
        
    /**
     * Initialises composer and installs creode docker tools
     * @return type
     */
    private function composerInit()
    {
        $this->_composer->setPath(
            $this->_input->getOption('composer')
        );

        $path = $this->_input->getOption('path');

        // init
        $this->_output->writeln('<info>Initialising composer</info>');

        if ($this->_fs->exists($path . '/composer.json')) {
            $this->_output->writeln('<comment>composer.json already exists, skipping</comment>');
            return;
        }

        $this->_composer->init($path, $this->_config['config']['docker']['package']);

        // install
        $this->_output->writeln('<info>Running composer install</info>');

        $this->_composer->install($path);
    }
}

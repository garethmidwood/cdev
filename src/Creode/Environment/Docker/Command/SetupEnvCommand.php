<?php
namespace Creode\Environment\Docker\Command;

use Creode\Cdev\Command\ConfigurationCommand;
use Creode\Cdev\Config;
use Creode\Environment\Docker\Docker;
use Creode\Environment\Docker\System\Compose\Compose;
use Creode\Environment\Docker\System\Sync\Sync;
use Creode\System\Composer\Composer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
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
                    'version' => '2',
                    'options' => [
                        'verbose' => true
                    ],
                    'syncs' => [
                    ]
                ],
                'compose' => [
                    'version' => '2',
                    'services' => []
                ]
            ]
        ]
    ];

    protected $_previousConfig;

    private $_containers = [
        'MySQL' => [
            'defaultActive' => true,
            'node' => 'mysql',
            'command' => Container\Mysql::COMMAND_NAME,
            'config' => Container\Mysql::CONFIG_DIR . '/' .
                Container\Mysql::CONFIG_FILE
        ],
        'PHP' => [
            'defaultActive' => true,
            'node' => 'php',
            'command' => Container\Php::COMMAND_NAME,
            'config' => Container\Php::CONFIG_DIR . '/' .
                Container\Php::CONFIG_FILE,
            'links' => [
                'mysql',
                'mailcatcher',
                'redis'
            ],
            'sync' => [
                'name' => 'project-website-code-sync',
                'default' => [
                    'src' => 'src',
                    'sync_userid' => 1000, # www-data
                    'sync_strategy' => 'unison',
                    'sync_excludes' => [
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
                ]
            ]
        ],
        'Mailcatcher' => [
            'defaultActive' => true,
            'node' => 'mailcatcher',
            'command' => Container\Mailcatcher::COMMAND_NAME,
            'config' => Container\Mailcatcher::CONFIG_DIR . '/' .
                Container\Mailcatcher::CONFIG_FILE
        ],
        'Redis' => [
            'defaultActive' => false,
            'node' => 'redis',
            'command' => Container\Redis::COMMAND_NAME,
            'config' => Container\Redis::CONFIG_DIR . '/' .
                Container\Redis::CONFIG_FILE
        ],
        'Drush' => [
            'defaultActive' => false,
            'node' => 'drush',
            'command' => Container\Drush::COMMAND_NAME,
            'config' => Container\Drush::CONFIG_DIR . '/' .
                Container\Drush::CONFIG_FILE,
            'depends' => [
                'php',
                'mysql'
            ],
            'frameworks' => [
                \Creode\Framework\Drupal8\Drupal8::NAME,
                \Creode\Framework\Drupal7\Drupal7::NAME
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

        $this->loadConfig($path . '/' . Config::CONFIG_DIR, Config::CONFIG_FILE, $output);

        $this->_previousConfig = $this->_config;

        $this->askQuestions();

        $this->saveConfig($path . '/' . Config::CONFIG_DIR, Config::CONFIG_FILE);
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

        $syncs = &$this->_config['config']['docker']['sync']['syncs'];

        unset($syncs['project-website-code-sync']);

        $previousProjectName = isset($this->_previousConfig['config']['docker']['name'])
                                ? $this->_previousConfig['config']['docker']['name']
                                : 'project';

        foreach($syncs as $name => $values) {
            $newName = str_replace(
                $previousProjectName,
                $this->_config['config']['docker']['name'],
                $name
            );

            $values['src'] = '../' . $this->_config['config']['dir']['src'];

            $default = implode(', ', $values['sync_excludes']);
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

            $values['sync_excludes'] = explode(',', $answer);

            unset($syncs[$name]);
            $syncs[$newName] = $values;
        }
    }

    /**
     * Asks questions to setup docker compose
     * @return null
     */
    private function askDockerComposeQuestions()
    {
        $helper = $this->getHelper('question');

        foreach($this->_containers as $label => $container) {
            $node = $container['node'];

            if (isset($container['frameworks'])) {
                if (!in_array(
                    $this->_config['config']['environment']['framework'],
                    $container['frameworks']
                )) {
                    $this->_output->writeln('<info>Skipping ' .$label . ' setup as ' . $this->_config['config']['environment']['framework'] . ' is not supported</info>');
                    $this->_config['config']['docker']['compose']['services'][$node]['active'] = false;
                    unset($this->_containers[$label]);
                    continue;
                }
            }

            if (isset($container['depends'])) {
                foreach($container['depends'] as $dependency) {
                    if (!$this->_config['config']['docker']['compose']['services'][$dependency]['active']) {
                        $this->_output->writeln('<info>Skipping ' .$label . ' setup as ' . $dependency . ' is not active</info>');
                        $this->_config['config']['docker']['compose']['services'][$node]['active'] = false;
                        unset($this->_containers[$label]);
                        continue;
                    }
                }
            }

            $useContainer = $this->containerRequired(
                $label,
                $this->_config['config']['docker']['compose']['services'][$node]['active'],
                $container['defaultActive']
            );

            if ($useContainer) {
                $this->configureContainer(
                    $label
                );
            } else {
                unset($this->_containers[$label]);
            }
        }

        // build links 
        foreach($this->_containers as $label => $container) {
            if (isset($container['links'])) {
                $activeLinks = [];

                foreach($container['links'] as $linkNode) {
                    if ($this->_config['config']['docker']['compose']['services'][$linkNode]['active']) {
                        $activeLinks[] = $linkNode;
                    }
                }

                $node = $container['node'];

                if (count($activeLinks) > 0) {
                    $this->_config['config']['docker']['compose']['services'][$node]['links'] = $activeLinks;
                } else {
                    unset($this->_config['config']['docker']['compose']['services'][$node]['links']);
                }
            }
        }
    }

    /**
     * Asks whether a container is required and saves results
     * @param string $label 
     * @param string|array &$config 
     * @param boolean $defaultActive
     * @return boolean
     */
    private function containerRequired($label, &$config, $defaultActive) 
    {
        $helper = $this->getHelper('question');

        $required = ((is_null($config) && $defaultActive) || $config);

        $optionsLabel = $required ? 'Y/n' : 'y/N';
        $question = new ConfirmationQuestion(
            '<question>' . $label . '? ' . $optionsLabel . '</question> : [Current: <info>' . ($required ? 'Yes' : 'No') . '</info>]',
            $required,
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

        $activeServices = [];

        foreach($this->_containers as $label => $values) {
            $configFile = Config::CONFIG_DIR . $values['config'];
            $config = Yaml::parse(file_get_contents($configFile));
            unset($config['active']);

            $links = $this->getContainerLinks($values['node']);
            if ($links) {
                $config['links'] = $links;
            } else {
                unset($config['links']);
            }

            $activeServices[$values['node']] = $config;
        }
        
        $configArray['version'] = '2';
        $configArray['services'] = $activeServices;
        

        //check if volumes var is null. If is dont add to config file
        $volumes = isset($this->_config['config']['docker']['compose']['volumes']) ? $this->_config['config']['docker']['compose']['volumes'] : null;
        if(!is_null($volumes)){
            $configArray['volumes'] = $volumes;
        }
        
        $this->saveConfig($path . '/' . Config::CONFIG_DIR, Compose::FILE, $configArray);
    }

    /**
     * Gets container links for a node
     * @param string $nodeName 
     * @return array
     */
    private function getContainerLinks($nodeName)
    {
        return isset($this->_config['config']['docker']['compose']['services'][$nodeName]['links'])
            ? $this->_config['config']['docker']['compose']['services'][$nodeName]['links']
            : false;
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

        $this->saveConfig($path . '/' . Config::CONFIG_DIR, Sync::FILE, $config);
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


    /**
     * Runs the configuration command for this container
     * @param String $containerName
     * @return Array
     */
    private function configureContainer($containerName)
    {
        $container = $this->_containers[$containerName];

        $command = $this->getApplication()->find($container['command']);
        $useDockerSync = $this->_config['config']['docker']['sync']['active'];

        $previousProjectName = isset($this->_previousConfig['config']['docker']['name'])
                                ? $this->_previousConfig['config']['docker']['name']
                                : 'project';

        if ($useDockerSync && isset($container['sync'])) {
            $sync = $container['sync'];

            $volumeName = str_replace(
                'project',
                $this->_config['config']['docker']['name'],
                $sync['name']
            );

            $previousVolumeName = str_replace(
                'project',
                $previousProjectName,
                $sync['name']
            );

            $syncData = $sync['default'];
            $syncData['src'] = $this->_config['config']['dir']['src'];

            unset($this->_config['config']['docker']['sync']['syncs'][$sync['name']]);
            unset($this->_config['config']['docker']['compose']['volumes'][$sync['name']]);
            unset($this->_config['config']['docker']['sync']['syncs'][$previousVolumeName]);
            unset($this->_config['config']['docker']['compose']['volumes'][$previousVolumeName]);
            $this->_config['config']['docker']['sync']['syncs'][$volumeName] = $syncData;
            $this->_config['config']['docker']['compose']['volumes'][$volumeName]['external'] = true;
        } else {
            $volumeName = false;
        } 

        $arguments = array(
            'command' => $command,
            '--path' => $this->_input->getOption('path'),
            '--src' => $this->_config['config']['dir']['src'],
            '--name' => $this->_config['config']['docker']['name'],
            '--port' => $this->_config['config']['docker']['port'],
            '--volume' => $volumeName
        );

        $cmdInput = new ArrayInput($arguments);
        $command->run($cmdInput, $this->_output);
    }
}

<?php
namespace Creode\Cdev\Command\Cdev;

use Creode\Cdev\Config;
use Creode\Environment;
use Creode\Framework;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class ConfigureCommand extends Command
{
    private $_config = array(
        'version' => '2',
        'config' => array(
            'dir' => array(
                'src' => null
            ),
            'environment' => array(
                'type' => 'docker',
                'framework' => null
            ),
            'backups' => array(
                'user' => 'creode',
                'host' => '192.168.0.97',
                'port' => '22',
                'db-dir' => 'e.g. /var/services/homes/creode/clients/{client}/database/',
                'db-file' => 'weekly-backup.sql',
                'media-dir' => 'e.g. /var/services/homes/creode/clients/{client}/media/',
                'media-file' => 'weekly-backup.tar',
            )
        )
    );

    // TODO: Get rid of these hard coded lists
    private $_environments = [
        'Environment\Docker\Docker'
    ];

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
        $this->setName('configure');
        $this->setDescription('Configures this repository for its environment and framework');

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

        $path = $this->_input->getOption('path');
        $configDir = $path . '/' . Config::CONFIG_DIR;
        $configFile = $configDir . Config::CONFIG_FILE;
        $servicesFile = $configDir . Config::SERVICES_FILE;

        if (file_exists($configFile)) {
            $this->_config = Yaml::parse(file_get_contents($configFile));
        }

        $answers = $this->askQuestions();

        $this->_output->writeln('Writing config file to ' . $configFile);
        $this->saveConfigFile($configFile);

        $this->_output->writeln('Writing services file to ' . $servicesFile);
        $this->saveServicesXml($servicesFile);

        $this->configureEnvironment($output);
    }

    /**
     * Asks the user questions
     * @return null
     */
    private function askQuestions()
    {
        $helper = $this->getHelper('question');
        $path = $this->_input->getOption('path');

        /**
         * 
         * DIRECTORY STRUCTURE
         * 
         */
        $originalSrc = $this->_config['config']['dir']['src'];

        $defaultSrc = isset($originalSrc) ? $originalSrc : 'src';

        $this->askQuestion(
            'Code directory',
            $this->_config['config']['dir']['src'],
            $defaultSrc
        );

        if ($this->_config['config']['dir']['src'] != $originalSrc) {
            $this->changeSrcDir($originalSrc, $this->_config['config']['dir']['src']);
        }


        /**
         * 
         * ENVIRONMENTAL / FRAMEWORK
         * 
         */
        $envs = [];
        foreach ($this->_environments as $env) {
            $envs[] = $env::NAME;
        }
var_dump($envs);

        $question = new ChoiceQuestion(
            'Environment type',
            array(
                // TODO: Find a better way to include these, ideally by adding the classes somehow
                Environment\Docker\Docker::NAME
            )
        );
        $question->setErrorMessage('Environment type %s is invalid.');
        $this->_config['config']['environment']['type'] = $helper->ask($this->_input, $this->_output, $question);


        $question = new ChoiceQuestion(
            'Framework',
            // TODO: Find a better way to include these, ideally by adding the classes somehow
            array(
                'magento1', // Framework\Magento1\Magento1::NAME,
                'magento2', // Framework\Magento2\Magento2::NAME,
                'drupal7',  // Framework\Drupal7\Drupal7::NAME,
                'drupal8',  // Framework\Drupal8\Drupal8::NAME,
                'wordpress' // Framework\WordPress\WordPress::NAME,
            )
        );
        $question->setErrorMessage('Framework %s is invalid.');
        $this->_config['config']['environment']['framework'] = $helper->ask($this->_input, $this->_output, $question);


        /**
         * 
         * BACKUPS
         * 
         */
        $this->askQuestion(
            'Backups: Host',
            $this->_config['config']['backups']['host']
        );

        $this->askQuestion(
            'Backups: Port',
            $this->_config['config']['backups']['port']
        );

        $this->askQuestion(
            'Backups: User',
            $this->_config['config']['backups']['user']
        );

        $this->askQuestion(
            'Backups: DB Directory',
            $this->_config['config']['backups']['db-dir']
        );

        $this->askQuestion(
            'Backups: DB file name',
            $this->_config['config']['backups']['db-file']
        );

        $this->askQuestion(
            'Backups: Media Directory',
            $this->_config['config']['backups']['media-dir']
        );

        $this->askQuestion(
            'Backups: Media file name',
            $this->_config['config']['backups']['media-file']
        );
    }

    /**
     * Convenience method for setting config based on results of questions
     * @param string $text 
     * @param string &$config Current config value
     * @return null
     */
    private function askQuestion(
        $text,
        &$config,
        $default = null
    ) {
        $helper = $this->getHelper('question');

        $current = isset($config) ? $config : $default;

        $question = new Question(
            $text . ' : [Current=' . $current . ']',
            $current
        );

        $config = $helper->ask($this->_input, $this->_output, $question);
    }

    /**
     * Saves project specific config file
     * @param string $configFile 
     * @return null
     */
    private function saveConfigFile($configFile)
    {
        $configDir = dirname($configFile);

        if (!file_exists($configDir)) {
            mkdir($configDir, 0744);
        }

        $configuration = Yaml::dump($this->_config);

        file_put_contents(
            $configFile,
            $configuration
        );
    }

    /**
     * Saves project specific service XML based on provided template
     * @param string $servicesFile 
     * @return null
     */
    private function saveServicesXml($servicesFile)
    {
        $configDir = dirname($servicesFile);
        
        if (!file_exists($configDir)) {
            mkdir($configDir, 0744);
        }

        $servicesContent = file_get_contents(__DIR__ . '/../../../../templates/services.env.xml');
        
        $searches = [
            '{{env_type}}',
            '{{env_framework}}'
        ];

        $replacements = [
            $this->_config['config']['environment']['type'],
            $this->_config['config']['environment']['framework']
        ];

        $servicesContent = str_replace($searches, $replacements, $servicesContent);

        file_put_contents(
            $servicesFile,
            $servicesContent
        );
    }

    /**
     * Changes (or sets up) the src directory for the site code
     * @param string|null $oldSrc 
     * @param string $newSrc 
     * @return null
     */
    private function changeSrcDir($oldSrc = null, $newSrc)
    {
        if (isset($oldSrc)) 
        {
            $this->renameSrcDir($oldSrc, $newSrc);
        } else {
            $this->createSrcDir($newSrc);
            $this->moveFilesToSrc($newSrc);
        }
    }

    /**
     * Renames an existing src directory
     * @param string $oldSrc 
     * @param string $src 
     * @return null
     */
    private function renameSrcDir($oldSrc, $src)
    {
        $this->_output->writeln('===== Renaming src directory');

        $path = $this->_input->getOption('path');

        $oldSrcPath = $path . '/' . $oldSrc;
        $srcPath = $path . '/' . $src;

        if (!$this->_fs->exists($oldSrcPath))
        {
            $this->_output->writeln("$oldSrc directory doesn't exist. Aborting");
            throw new \Exception("$oldSrc directory doesn't exist");
        }
            
        $this->_output->writeln("Renaming $oldSrc directory to $src");

        $this->_fs->rename(
            $oldSrcPath,
            $srcPath 
        );
    }

    /**
     * Creates src directory
     * @param string $src 
     * @return null
     */
    private function createSrcDir($src)
    {
        $this->_output->writeln('===== Creating src directory');

        $path = $this->_input->getOption('path');

        $srcPath = $path . '/' . $src;

        if ($this->_fs->exists($srcPath))
        {
            $this->_output->writeln("$src directory already exists. Continuing with existing dir");
            return;
        }
            
        $this->_output->writeln("Creating $src directory");
        
        $this->_fs->mkdir($srcPath, 0740);

        $this->_output->writeln("$src directory created");
    }

    /**
     * Moves files into the src directory
     * @param string $src 
     * @return null
     */
    private function moveFilesToSrc($src)
    {
        $this->_output->writeln('===== Moving files to src directory');

        $path = $this->_input->getOption('path');

        $this->_finder
            ->in($path)
            ->depth('== 0')
            ->exclude($src);

        if (count($this->_finder) == 0) {
            $this->_output->writeln("No files to move");
            return;
        }

        foreach ($this->_finder as $file) {
            $this->_output->writeln("Moving {$file->getFileName()} into $src directory");

            $this->_fs->rename(
                $file->getPath() . '/' . $file->getFileName(),
                $file->getPath() . '/' . $src . '/' . $file->getFileName() 
            );
        }
    }

    /**
     * Runs the setup for the selected environment
     * @param OutputInterface $output 
     * @return null
     */
    private function configureEnvironment(OutputInterface $output)
    {
        $cmdNamespace = $this->_environment::COMMAND_NAMESPACE;

        // TODO: This command name should be enforced by the environment class
        // will probably need a new abstract class to enable that
        $cmd = $this->_config['config']['environment']['type'] . ':setup';

        $command = $this->getApplication()->find($cmd);

        $arguments = array(
            'command' => $cmd
        );

        $cmdInput = new ArrayInput($arguments);

        $command->run($cmdInput, $output);
    }
}

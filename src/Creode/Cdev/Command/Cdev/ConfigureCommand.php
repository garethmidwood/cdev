<?php
namespace Creode\Cdev\Command\Cdev;

use Creode\Cdev\Command\ConfigurationCommand;
use Creode\Cdev\Config;
use Creode\Collections\EnvironmentCollection;
use Creode\Collections\FrameworkCollection;
use Creode\Environment;
use Creode\Framework;
use Creode\System\Git\Git;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ConfigureCommand extends ConfigurationCommand
{
    protected $_config = array(
        'version' => '2',
        'config' => array(
            'dir' => array(
                'wrapper-repo' => false,
                'src' => null
            ),
            'environment' => array(
                'type' => null,
                'framework' => null
            )
        )
    );

    /**
     * @var string
     */
    private $_chosenEnvironmentClass;

    /**
     * @var string
     */
    private $_chosenFrameworkClass;

    /**
     * @var array
     */
    private $_environments;

    /**
     * @var array
     */
    private $_frameworks;

    /**
     * @var Filesystem
     */
    protected $_fs;

    /**
     * @var Finder
     */
    protected $_finder;

    /**
     * @var Git
     */
    protected $_git;



    /**
     * Constructor
     * @param Filesystem $fs
     * @param Finder $finder
     * @return null
     */
    public function __construct(
        Filesystem $fs,
        Finder $finder,
        Git $git,
        EnvironmentCollection $environmentCollection,
        FrameworkCollection $frameworkCollection
    ) {
        $this->_fs = $fs;
        $this->_finder = $finder;
        $this->_git = $git;
        $this->_environments = $environmentCollection->getItems();
        $this->_frameworks = $frameworkCollection->getItems();

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

        $this->loadConfig(Config::getGlobalConfigDir(), Config::CONFIG_FILE, $output);
        $this->loadConfig($path . '/' . Config::CONFIG_DIR, Config::CONFIG_FILE, $output);

        $this->askQuestions();

        $this->saveConfig($path . '/' . Config::CONFIG_DIR, Config::CONFIG_FILE);
        $this->saveServicesXml($path);

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


        $originalSrc = $this->_config['config']['dir']['src'];
        $defaultSrc = isset($originalSrc) ? $originalSrc : 'src';

        $originalWrapperRepo = $this->_config['config']['dir']['wrapper-repo'];
        $alreadyUsingWrapperRepo = isset($originalWrapperRepo) ? $originalWrapperRepo : false;

        // you can't stop using a wrapper repo if you already configured it
        if (!$alreadyUsingWrapperRepo) {
            /**
             * 
             * WRAPPER REPOSITORY
             * 
             */

            $default = false;
            $optionsLabel = $default ? 'Y/n' : 'y/N';
            $question = new ConfirmationQuestion(
                '<question>Use wrapper repository? ' . $optionsLabel . '</question> : [Current: <info>' . ($default ? 'Yes' : 'No') . '</info>]',
                $default,
                '/^(y|j)/i'
            );
            $this->_config['config']['dir']['wrapper-repo'] = $helper->ask($this->_input, $this->_output, $question);


            if ($this->_config['config']['dir']['wrapper-repo']) {
                $this->_config['config']['dir']['src'] = $defaultSrc;
                $this->createWrapperRepo($originalSrc, $defaultSrc);
            } else {
                /**
                 * 
                 * DIRECTORY STRUCTURE
                 * 
                 */
                $this->askQuestion(
                    'Code directory',
                    $this->_config['config']['dir']['src'],
                    $defaultSrc
                );

                if ($this->_config['config']['dir']['src'] != $originalSrc) {
                    $this->changeSrcDir($originalSrc, $this->_config['config']['dir']['src']);
                }
            }
        }

        /**
         * 
         * ENVIRONMENT
         * 
         */
        $envs = [];
        $envMap = [];
        foreach ($this->_environments as $env) {
            $envs[$env::NAME] = $env::LABEL;
            $envMap[$env::NAME] = $env;
        }

        $defaultEnv = count($envs) == 1 ? $envs[key($envs)] : $this->_config['config']['environment']['type'];

        $question = new ChoiceQuestion(
            '<question>Environment type</question> : [Current: <info>' . (isset($defaultEnv) ? $defaultEnv : 'None') . '</info>]',
            $envs,
            $defaultEnv
        );
        $question->setErrorMessage('Environment type %s is invalid.');
        $this->_config['config']['environment']['type'] = $helper->ask($this->_input, $this->_output, $question);
        $this->_chosenEnvironmentClass = $envMap[$this->_config['config']['environment']['type']];


        /**
         * 
         * FRAMEWORK
         * 
         */
        $frameworks = [];
        $frameworkMap = [];
        foreach ($this->_frameworks as $framework) {
            $frameworks[$framework::NAME] = $framework::LABEL;
            $frameworkMap[$framework::NAME] = $framework;
        }

        $defaultFramework = count($frameworks) == 1 ? $frameworks[key($frameworks)] : $this->_config['config']['environment']['framework'];

        $question = new ChoiceQuestion(
            '<question>Framework:</question> [Current: <info>' . (isset($defaultFramework) ? $defaultFramework : 'None') . '</info>]',
            $frameworks,
            $defaultFramework
        );
        $question->setErrorMessage('Framework %s is invalid.');
        $this->_config['config']['environment']['framework'] = $helper->ask($this->_input, $this->_output, $question);
        $this->_chosenFrameworkClass = $frameworkMap[$this->_config['config']['environment']['framework']];

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
     * Saves project specific service XML based on provided template
     * @param string $servicesFile 
     * @return null
     */
    private function saveServicesXml($path)
    {
        $configDir = $path . '/' . Config::CONFIG_DIR;
        $servicesFile = $configDir . Config::SERVICES_FILE;
        
        if (!file_exists($configDir)) {
            $this->_output->writeln('<info>Creating config directory</info>');
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

        $this->_output->writeln('<info>Writing services file ' . $servicesFile . '</info>');
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
    private function changeSrcDir($oldSrc = null, $newSrc, $moveRepo = false)
    {
        if (isset($oldSrc))
        {
            $this->renameSrcDir($oldSrc, $newSrc);
        } else {
            if ($this->createSrcDir($newSrc)) {
                $this->moveFilesToSrc($newSrc, $moveRepo);
            }
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

        if ($oldSrcPath == $srcPath)
        {
            $this->_output->writeln("old and new directories are the same, skipping rename");
            return;
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
        $this->_output->writeln('<info>===== Creating src directory</info>');

        $path = $this->_input->getOption('path');

        $srcPath = $path . '/' . $src;

        if ($this->_fs->exists($srcPath))
        {
            $this->_output->writeln("<comment>$src directory already exists. Continuing with existing dir</comment>");
            return false;
        }
            
        $this->_output->writeln("<comment>Creating $src directory</comment>");
        
        $this->_fs->mkdir($srcPath, 0740);

        $this->_output->writeln("<comment>$src directory created</comment>");

        return true;
    }

    /**
     * Moves files into the src directory
     * @param string $src 
     * @return null
     */
    private function moveFilesToSrc($src, $moveRepo = false)
    {
        $path = $this->_input->getOption('path');

        $exclusions = [$src];
        if (!$moveRepo) {
            $this->_output->writeln('<info>===== Moving files to src directory</info>');
            $exclusions[] = '.git';
        } else {
            $this->_output->writeln("<info>===== Moving entire repository to src directory</info>");
        }

        $this->_finder
            ->in($path)
            ->depth('== 0')
            ->ignoreDotFiles(false)
            ->exclude($exclusions);

        if (count($this->_finder) == 0) {
            $this->_output->writeln("<comment>No files to move</comment>");
            return;
        }

        foreach ($this->_finder as $file) {
            $this->_output->writeln("<comment>Moving {$file->getFileName()} into $src directory</comment>");

            $this->_fs->rename(
                $file->getPath() . '/' . $file->getFileName(),
                $file->getPath() . '/' . $src . '/' . $file->getFileName() 
            );
        }

        if ($moveRepo) {
            $this->_fs->rename(
                $file->getPath() . '/.git',
                $file->getPath() . '/' . $src . '/.git' 
            );
        }
    }


    /**
     * Sets up a wrapper repository for the current repo
     * @param string|null $oldSrc 
     * @param string $newSrc 
     * @return null
     */
    private function createWrapperRepo($oldSrc = null, $newSrc)
    {
        $this->_output->writeln('===== Creating wrapper repository');

        $path = $this->_input->getOption('path');

        // move code into src dir
        $this->changeSrcDir($oldSrc, $newSrc, true);

        // initialise this as a repo
        $this->_git->init($path);

        // set up the gitmodules file
        $repo = $this->_git->getRepoURL($path . '/' . $newSrc);
        if (!$repo) {
            throw new \Exception('You do not appear to be in a repository, or the repo does not have an origin remote');
        }

        $submoduleTemplate = <<<GITSUBMODULE
[submodule "{$newSrc}"]
    path = {$newSrc}
    url = {$repo}
GITSUBMODULE;

        $this->_fs->dumpFile('.gitmodules', $submoduleTemplate);

        // TODO: This shouldn't include docker specifics
        $gitignoreTemplate = <<<GITIGNORE
.docker-sync
db/backup.sql
GITIGNORE;
    
        $this->_fs->dumpFile('.gitignore', $gitignoreTemplate);

        $this->_git->add($path);

        $this->_git->commit($path, 'Initial cdev wrapper repo setup');
    }

    /**
     * Runs the setup for the selected environment
     * @param OutputInterface $output 
     * @return null
     */
    private function configureEnvironment(OutputInterface $output)
    {
        $cmdNamespace = $this->_chosenEnvironmentClass::COMMAND_NAMESPACE;

        // TODO: This command name should be enforced by the environment class
        // will probably need a new abstract class to enable that
        $cmd = $cmdNamespace . ':setup';

        $command = $this->getApplication()->find($cmd);

        $arguments = array(
            'command' => $cmd
        );

        $cmdInput = new ArrayInput($arguments);

        $command->run($cmdInput, $output);
    }
}

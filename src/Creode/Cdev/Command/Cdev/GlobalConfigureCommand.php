<?php
namespace Creode\Cdev\Command\Cdev;

use Creode\Cdev\Command\ConfigurationCommand;
use Creode\Cdev\Config;
use Creode\Collections\FrameworkCollection;
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

class GlobalConfigureCommand extends ConfigurationCommand
{
    /**
     * @var array
     */
    private $_frameworks;

    protected $_config = array(
        'version' => '2',
        'config' => array(
            'backups' => array(
                'user' => null,
                'host' => null,
                'port' => null,
                'db-dir' => null,
                'db-file' => null,
                'media-dir' => null,
                'media-file' => null,
            )
        )
    );

    public function __construct(
        FrameworkCollection $frameworkCollection
    ) {
        $this->_frameworks = $frameworkCollection->getItems();

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('global:configure');
        $this->setDescription('Configures your machines cdev defaults');

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

        $this->askQuestions();

        $this->saveConfig(Config::getGlobalConfigDir(), Config::CONFIG_FILE);
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


        /**
         * 
         * ENVIRONMENT
         * 
         */
        // $envs = [];
        // $envMap = [];
        // foreach ($this->_environments as $env) {
        //     $envs[$env::NAME] = $env::LABEL;
        //     $envMap[$env::NAME] = $env;
        // }

        // $defaultEnv = count($envs) == 1 ? $envs[key($envs)] : $this->_config['config']['environment']['type'];

        // $question = new ChoiceQuestion(
        //     '<question>Environment type</question> : [Current: <info>' . (isset($defaultEnv) ? $defaultEnv : 'None') . '</info>]',
        //     $envs,
        //     $defaultEnv
        // );
        // $question->setErrorMessage('Environment type %s is invalid.');
        // $this->_config['config']['environment']['type'] = $helper->ask($this->_input, $this->_output, $question);
        // $this->_chosenEnvironmentClass = $envMap[$this->_config['config']['environment']['type']];

        /**
         * 
         * Boilerplate sites
         * 
         */
        $this->_setupFrameworkBoilerplates();
    }


    private function _setupFrameworkBoilerplates() 
    {
        $frameworks = [];
        $frameworkMap = [];
        foreach ($this->_frameworks as $framework) {
            $frameworks[$framework::NAME] = $framework::LABEL;
            $frameworkMap[$framework::NAME] = $framework;

            $editBoilerplates = false;

            $this->askYesNoQuestion(
                'Edit boilerplates for ' . $framework::NAME,
                $editBoilerplates
            );

            if ($editBoilerplates) {
                $this->_editBoilerplates($framework);
            }
        }
    }

    private function _editBoilerplates($framework)
    {
        if (isset($this->_config['config']['boilerplates'][$framework::NAME]['boilerplates'])
            && count($this->_config['config']['boilerplates'][$framework::NAME]['boilerplates']) > 0
        ) {
            $this->_removeBoilerplates($framework);
        }

        $this->_addBoilerplates($framework);
    }

    private function _removeBoilerplates($framework)
    {
        $removeBoilerplates = false;

        $this->askYesNoQuestion(
            'Remove boilerplates',
            $removeBoilerplates
        );

        if (!$removeBoilerplates) {
            return;
        }

        foreach($this->_config['config']['boilerplates'][$framework::NAME]['boilerplates'] as $index => $repo) {
            $removeRepo = false;

            $repoName = is_string($repo)
                ? $repo . ' [master]'
                :  $repo['repo'] . ' [' . $repo['branch'] . ']';

            $this->askYesNoQuestion(
                'Remove ' . $repoName,
                $removeRepo
            );

            if ($removeRepo) {
                unset(
                    $this->_config['config']['boilerplates'][$framework::NAME]['boilerplates'][$index]
                );
            }   
        }
    }

    private function _addBoilerplates($framework)
    {
        $addNewBoilerplate = false;

        $this->askYesNoQuestion(
            'Add new boilerplate',
            $addNewBoilerplate
        );

        if (!$addNewBoilerplate) {
            return;
        }

        $repo = $branch = null;

        $this->askQuestion(
            'Repo address',
            $repo
        );

        $this->askQuestion(
            'Repo branch',
            $branch,
            'master'
        );

        $this->_config['config']['boilerplates'][$framework::NAME]['boilerplates'][] = ['repo' => $repo, 'branch' => $branch];

        // offer to add another
        $this->_addBoilerplates($framework);
    }
}

<?php
namespace Creode\Cdev\Command\Cdev;

use Creode\Cdev\Command\ConfigurationCommand;
use Creode\Cdev\Config;
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

        // /**
        //  * 
        //  * ENVIRONMENT
        //  * 
        //  */
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
}

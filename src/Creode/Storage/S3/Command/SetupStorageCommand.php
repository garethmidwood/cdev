<?php
namespace Creode\Storage\S3\Command;

use Creode\Cdev\Command\ConfigurationCommand;
use Creode\Cdev\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class SetupStorageCommand extends ConfigurationCommand
{
    protected $_config = [
        'config' => [
            'storage' => [
                'bucket' => null,
                'db-dir' => 'databases',
                'media-dir' => 'media'
            ]
        ]
    ];

    protected function configure()
    {
        $this->setName('storage:s3:setup');
        $this->setHidden(true);
        $this->setDescription('Sets up the s3 storage config');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
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

        $default = $this->_config['config']['storage']['bucket'];
        $question = new Question(
            '<question>Bucket name</question> : [Current: <info>' . (isset($default) ? $default : 'None') . '</info>]',
            $default
        );
        $this->_config['config']['storage']['bucket'] = $helper->ask($this->_input, $this->_output, $question);


        $default = $this->_config['config']['storage']['db-dir'];
        $question = new Question(
            '<question>Database Directory Name</question> : [Current: <info>' . (isset($default) ? $default : 'None') . '</info>]',
            $default
        );
        $this->_config['config']['storage']['db-dir'] = $helper->ask($this->_input, $this->_output, $question);


        $default = $this->_config['config']['storage']['media-dir'];
        $question = new Question(
            '<question>Media Directory Name</question> : [Current: <info>' . (isset($default) ? $default : 'None') . '</info>]',
            $default
        );
        $this->_config['config']['storage']['media-dir'] = $helper->ask($this->_input, $this->_output, $question);  
    }
}

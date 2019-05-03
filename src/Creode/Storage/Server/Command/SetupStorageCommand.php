<?php
namespace Creode\Storage\Server\Command;

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
                'user' => null,
                'host' => null,
                'port' => null,
                'db-dir' => 'databases',
                'media-dir' => 'media'
            ]
        ]
    ];

    protected function configure()
    {
        $this->setName('storage:server:setup');
        $this->setHidden(true);
        $this->setDescription('Sets up server (SSH) storage config');

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

        $default = $this->_config['config']['storage']['user'];
        $question = new Question(
            '<question>SSH Username</question> : [Current: <info>' . (isset($default) ? $default : 'None') . '</info>]',
            $default
        );
        $this->_config['config']['storage']['user'] = $helper->ask($this->_input, $this->_output, $question);


        $default = $this->_config['config']['storage']['host'];
        $question = new Question(
            '<question>SSH Host</question> : [Current: <info>' . (isset($default) ? $default : 'localhost') . '</info>]',
            $default
        );
        $this->_config['config']['storage']['host'] = $helper->ask($this->_input, $this->_output, $question);


        $default = isset($this->_config['config']['storage']['port']) ? $this->_config['config']['storage']['port'] : '22';
        $question = new Question(
            '<question>SSH Port</question> : [Current: <info>' . $default . '</info>]',
            $default
        );
        $question->setValidator(function ($answer) {
            if (!preg_match('/^[0-9]{2,5}$/', $answer)) {
                throw new \RuntimeException(
                    'Port number must be a number between 2 and 5 digits'
                );
            }

            return $answer;
        });
        $this->_config['config']['storage']['port'] = $helper->ask($this->_input, $this->_output, $question);


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

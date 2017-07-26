<?php

namespace Creode\Environment\Docker\Command\Container;

use Creode\Cdev\Config;
use Creode\Cdev\Command\ConfigurationCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

abstract class Container extends ConfigurationCommand
{
    const CONFIG_DIR = 'containers';

    protected abstract function askQuestions();

    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription(static::COMMAND_DESC);
        $this->setHidden(true);

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );

        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'Current configuration',
            []
        );

        $this->addOption(
            'name',
            'm',
            InputOption::VALUE_REQUIRED,
            'Docker project name',
            'docker'
        );

        $this->addOption(
            'port',
            'o',
            InputOption::VALUE_REQUIRED,
            'Docker port number',
            '000'
        );

        $this->addOption(
            'src',
            's',
            InputOption::VALUE_REQUIRED,
            'Code source directory',
            'src'
        );
    }

    /**
     * Executes the command
     * @param InputInterface $input 
     * @param OutputInterface $output 
     * @return type
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_input = $input;
        $this->_output = $output;

        $path = $this->_input->getOption('path');

        $containerConfigDir = Config::CONFIG_DIR . self::CONFIG_DIR . '/';

        $this->loadConfig($path, $containerConfigDir, static::CONFIG_FILE, $output);

        $this->askQuestions();

        $this->saveConfig($path, $containerConfigDir, static::CONFIG_FILE);
    }

    /**
     * Asks whether to use an image or build the image from local scripts
     * @param string $defaultBuild 
     * @param string $defaultImage 
     * @param array &$config
     * @param array $builds
     * @param array $images
     */
    protected function buildOrImage(
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
}

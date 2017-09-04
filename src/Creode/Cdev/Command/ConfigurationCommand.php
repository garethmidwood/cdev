<?php

namespace Creode\Cdev\Command;

use Creode\Cdev\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

abstract class ConfigurationCommand extends Command
{
    protected $_config = array();

    /**
     * Loads a config file
     * @param string $dir 
     * @param string $file 
     * @param OutputInterface $output 
     * @return null
     */
    protected function loadConfig(
        $dir, 
        $file,
        OutputInterface $output
    ) {
        $configFile = $dir . $file;

        if (file_exists($configFile)) {
            $output->writeln('<info>Loading config file ' . $configFile . '</info>');
            $this->_config = array_replace_recursive($this->_config, Yaml::parse(file_get_contents($configFile)));
        }
    }

    /**
     * Saves config file
     * @param string $dir 
     * @param string $file 
     * @param null|array $config
     * @return null
     */
    protected function saveConfig(
        $dir, 
        $file,
        array $config = null
    ) {
        $config = isset($config) ? $config : $this->_config;

        $configFile = $dir . $file;

        if (!file_exists($dir)) {
            $this->_output->writeln('<info>Creating config directory ' . $dir . '</info>');
            mkdir($dir, 0744);
        }

        $configuration = Yaml::dump($config);

        $this->_output->writeln('<info>Saving config file ' .$dir . $file . '</info>');

        file_put_contents(
            $configFile,
            $configuration
        );
    }

    /**
     * Convenience method for setting config based on results of questions
     * @param string $text 
     * @param string &$config Current config value
     * @return null
     */
    protected function askQuestion(
        $text,
        &$config,
        $default = null
    ) {
        $helper = $this->getHelper('question');

        $current = isset($config) ? $config : $default;

        $question = new Question(
            '<question>' . $text . '</question> : [Current: <info>' . $current . '</info>] ',
            $current
        );

        // TODO: input and output don't exist here yet! Well maybe they do .. argh
        $config = $helper->ask($this->_input, $this->_output, $question);
    }

    /**
     * Convenience method for setting config based on results of yes/no
     * @param string $text 
     * @param string &$config Current config value
     * @return null
     */
    protected function askYesNoQuestion(
        $text,
        &$config,
        $default = false
    ) {
        $helper = $this->getHelper('question');

        $current = isset($config) ? $config : $default;

        $optionsLabel = $default ? 'Y/n' : 'y/N';
        $question = new ConfirmationQuestion(
            '<question>' . $text . '</question> : [Current: <info>' . ($current ? 'Yes' : 'No') . '</info>]',
            $current,
            '/^(y|j)/i'
        );

        // TODO: input and output don't exist here yet! Well maybe they do .. argh
        $config = $helper->ask($this->_input, $this->_output, $question);
    }
}

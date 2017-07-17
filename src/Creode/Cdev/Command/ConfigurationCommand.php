<?php

namespace Creode\Cdev\Command;

use Creode\Cdev\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

abstract class ConfigurationCommand extends Command
{
    protected $_config = array();

    /**
     * Loads config file
     * @param string $path 
     * @return null
     */
    protected function loadConfig($path, OutputInterface $output) 
    {
        $configDir = $path . '/' . Config::CONFIG_DIR;
        $configFile = $configDir . Config::CONFIG_FILE;

        if (file_exists($configFile)) {
            $output->writeln('<info>Loading config file</info>');
            $this->_config = array_replace_recursive($this->_config, Yaml::parse(file_get_contents($configFile)));
        }
    }

    /**
     * Saves config file
     * @param string $path 
     * @return null
     */
    protected function saveConfig($path)
    {
        $configDir = $path . '/' . Config::CONFIG_DIR;
        $configFile = $configDir . Config::CONFIG_FILE;

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
}

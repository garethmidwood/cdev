<?php

namespace Creode\Cdev\Command;

use Creode\Cdev\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
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
            $output->writeln('Loading config file');
            $this->_config = Yaml::parse(file_get_contents($configFile));
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
}

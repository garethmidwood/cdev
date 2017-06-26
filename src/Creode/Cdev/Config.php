<?php
namespace Creode\Cdev;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Config
{
    const CONFIG_FILE = 'cdev.yml';

    private $_config = array();

    /**
     * @param Filesystem $fs
     * @return null
     */
    public function __construct(ConsoleOutput $output)
    {
        // TODO: Move these styles into a new output class with methods, e.g. ->warning() ->notice()
        $style = new OutputFormatterStyle('black', 'yellow', array('bold', 'blink'));
        $output->getFormatter()->setStyle('warning', $style);

        $this->loadConfig($output);
    }

    private function loadConfig(ConsoleOutput $output) {
        if (!file_exists(self::CONFIG_FILE)) {
            $output->writeln('<warning>Config file ' . self::CONFIG_FILE . ' not found. Run cdev:configure</warning>');
            return;
        }

        $config = Yaml::parse(file_get_contents(self::CONFIG_FILE));

        if (!isset($config['config'])) {
            throw new \Exception('Config file is missing root config node');
        }

        $this->_config = $config['config'];
    }

    /**
     * Gets a value from the config
     * @param string $key 
     * @param mixed|bool $defaultValue 
     * @return mixed|bool
     */
    public function get($key, $defaultValue = false) {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }

        return $defaultValue;        
    }
}

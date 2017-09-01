<?php
namespace Creode\Cdev;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Config
{
    const CONFIG_FILE = 'cdev.yml';
    const SERVICES_FILE = 'services.env.xml';
    const CONFIG_DIR = 'cdev/';
    const GLOBAL_CONFIG_DIR = '.cdev/';

    /**
     * @var array
     */
    private $_config = array();

    /**
     * @var bool
     */
    private $_configured = true;

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

    private function loadConfig(ConsoleOutput $output)
    {
        // load global config
        $this->loadConfigFile(
            $output,
            self::getGlobalConfigDir() . self::CONFIG_FILE,
            'Global config file ' . self::getGlobalConfigDir() . self::CONFIG_FILE . ' not found. Run cdev global:configure'
        );

        // load local config
        $this->loadConfigFile(
            $output,
            self::getLocalConfigDir() . self::CONFIG_FILE,
            'Project config file ' . self::getLocalConfigDir() . self::CONFIG_FILE . ' not found. Run cdev configure'
        );
    }

    private function loadConfigFile(ConsoleOutput $output, $file, $error)
    {
        if (!file_exists($file)) {
            $this->_configured = false;
            $output->writeln('<warning>' . $error . '</warning>');
            return;
        }

        $config = Yaml::parse(file_get_contents($file));

        if (!isset($config['config'])) {
            throw new \Exception('Config file is missing root config node');
        }

        $this->_config = array_merge($this->_config, $config['config']);
    }

    /**
     * Gets a value from the config
     * @param string $key 
     * @param mixed|bool $defaultValue 
     * @return mixed|bool
     */
    public function get($key, $defaultValue = false)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }

        return $defaultValue;        
    }

    /**
     * Does the config file exist?
     * @return bool
     */
    public function isConfigured()
    {
        return $this->_configured;
    }

    /**
     * Returns path to global config directory
     * @return string
     */
    public static function getGlobalConfigDir()
    {
        return getenv('HOME') . '/' . self::GLOBAL_CONFIG_DIR;
    }

    /**
     * Returns path to local config directory
     * @return string
     */
    public static function getLocalConfigDir()
    {
        return self::CONFIG_DIR;
    }
}

<?php

namespace Creode\Cdev\Plugin;

use Creode\Collections\FrameworkCollection;
use Creode\Collections\EnvironmentCollection;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class Manager
{
    CONST SERVICES_FILE = 'cdev.services.xml';
    CONST MODULE_FILE = 'cdev.module.yml';
    CONST PLUGIN_DIR = DIRECTORY_SEPARATOR . '.cdev' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR;

    static private function getPluginDir()
    {
        return getenv("HOME") . self::PLUGIN_DIR;
    }

    /**
     * Initialises the plugin directory and brings in autoload
     * @return void
     */
    static public function init() 
    {
        $cdevPlugins = self::getPluginDir();

        if (!is_dir($cdevPlugins)) {
            if (!mkdir($cdevPlugins, 0700, true)) {
                die('ERROR: Could not create plugin dir ' . $cdevPlugins . PHP_EOL);
            }
        }

        // include plugin autoload
        if (file_exists($cdevPlugins . '/vendor/autoload.php')) {
            include $cdevPlugins . '/vendor/autoload.php';
        }
    }


    /**
     * Registers the plugins services
     * @param XmlFileLoader $loader 
     * @return void
     */
    static public function registerServices(
        XmlFileLoader $loader
    ) {
        $finder = new Finder();
        $finder->files()->name(self::SERVICES_FILE)->in(self::getPluginDir());

        foreach ($finder as $file) {
            $loader->load($file->getRealPath());
        }
    }

    /**
     * Registers the plugins commands
     * @param ContainerBuilder $container 
     * @param Application $application
     * @return void
     */
    static public function registerCommands(
        ContainerBuilder $container,
        Application $application
    ) {
        $finder = new Finder();
        $finder->files()->name(self::MODULE_FILE)->in(self::getPluginDir());

        foreach ($finder as $file) {
            $contents = Yaml::parse(file_get_contents($file->getRealPath()));
            
            if (is_array($contents) && 
                isset($contents['commands']) &&
                is_array($contents['commands']) &&
                count($contents['commands']) > 0
            ) {
                foreach($contents['commands'] as $commandId) {
                    $command = $container->get($commandId);
                    $application->add($command);
                }
            }
        }
    }

    /**
     * Adds the plugin frameworks to the collection
     * @param FrameworkCollection $frameworkCollection 
     * @return void
     */
    static public function registerFrameworks(
        FrameworkCollection $frameworkCollection
    ) {
        $finder = new Finder();
        $finder->files()->name(self::MODULE_FILE)->in(self::getPluginDir());

        foreach ($finder as $file) {
            $contents = Yaml::parse(file_get_contents($file->getRealPath()));
            
            if (is_array($contents) && 
                isset($contents['frameworks']) &&
                is_array($contents['frameworks']) &&
                count($contents['frameworks']) > 0
            ) {
                foreach($contents['frameworks'] as $frameworkClass) {
                    if (!class_exists($frameworkClass)) {
                        die('Could not find plugin framework class ' . $frameworkClass . PHP_EOL);
                    }

                    $frameworkCollection->addItem($frameworkClass);
                }
            }
        }
    }

    /**
     * Adds the plugin environments to the collection
     * @param EnvironmentCollection $environmentCollection 
     * @return void
     */
    static public function registerEnvironments(
        EnvironmentCollection $environmentCollection
    ) {
        $finder = new Finder();
        $finder->files()->name(self::MODULE_FILE)->in(self::getPluginDir());

        foreach ($finder as $file) {
            $contents = Yaml::parse(file_get_contents($file->getRealPath()));
            
            if (is_array($contents) && 
                isset($contents['environments']) &&
                is_array($contents['environments']) &&
                count($contents['environments']) > 0
            ) {
                foreach($contents['environments'] as $environmentClass) {
                    if (!class_exists($environmentClass)) {
                        die('Could not find plugin environment class ' . $environmentClass . PHP_EOL);
                    }

                    $environmentCollection->addItem($environmentClass);
                }
            }
        }
    }
}

<?php
/**
 * 
 * version: @package_version@
 * 
 * 
 */

use Creode\Cdev\Config;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;

require __DIR__.'/vendor/autoload.php';

$container = new ContainerBuilder();
$loader = new XmlFileLoader($container, new FileLocator(__DIR__));
$loader->load('services.xml');


// Initialise the plugins
\Creode\Cdev\Plugin\Manager::init();

// load plugin services
\Creode\Cdev\Plugin\Manager::registerServices($loader);

// load project config
$localServiceConfig = getcwd() . '/' . Config::CONFIG_DIR . Config::SERVICES_FILE;

if (file_exists($localServiceConfig)) {
    $loader->load($localServiceConfig);
}


try {
    // Create application so we can register additional commands in plugins
    $application = $container->get('symfony.application');
} catch (ServiceNotFoundException $e) {
    echo "FATAL ERROR: One of the services could not be found, do you need to install a plugin for it? Try `cdev plugin:search`" . PHP_EOL;
    echo $e->getMessage();
    echo PHP_EOL;
    exit;
}

// register commands from plugins
\Creode\Cdev\Plugin\Manager::registerCommands($container, $application);



// run the app
$output = $container->get('symfony.console_output');
$application->run(null, $output);

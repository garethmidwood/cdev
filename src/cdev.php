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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

require __DIR__.'/vendor/autoload.php';

$container = new ContainerBuilder();
$loader = new XmlFileLoader($container, new FileLocator(__DIR__));
$loader->load('services.xml');

$localServiceConfig = getcwd() . '/' . Config::CONFIG_DIR . Config::SERVICES_FILE;

if (file_exists($localServiceConfig)) {
    $loader->load($localServiceConfig);
}

$output = $container->get('symfony.console_output');

$application = $container->get('symfony.application');
$application->run(null, $output);

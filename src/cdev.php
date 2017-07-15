<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

require __DIR__.'/vendor/autoload.php';

$container = new ContainerBuilder();
$loader = new XmlFileLoader($container, new FileLocator(__DIR__));
$loader->load('services.xml');

if (file_exists(getcwd() . '/config/services.env.xml')) {
    $loader->load(getcwd() . '/config/services.env.xml');
}

$output = $container->get('symfony.console_output');

$application = $container->get('symfony.application');
$application->run(null, $output);

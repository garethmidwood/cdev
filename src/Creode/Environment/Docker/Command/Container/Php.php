<?php
namespace Creode\Environment\Docker\Command\Container;

class Php extends Container
{
    const COMMAND_NAME = 'container:php:configure';
    const COMMAND_DESC = 'Configures the PHP container';
    const CONFIG_FILE = 'php.yml';
    const CONFIG_NODE = 'php';

    protected $_config = 
    [
        'active' => true,
        'container_name' => 'project_php',
        'ports' => [
            '80:80'
        ],
        'environment' => [
            'VIRTUAL_HOST' => '.project.docker'
        ],
        'links' => [
            // TODO these links need to be added somehow..
            'mysql:mysql',
            'mailcatcher:mailcatcher',
            'redis'
        ],
        'volumes' => [
            ['../src:/var/www/html']
        ]
    ];

    protected function askQuestions()
    {
        $path = $this->_input->getOption('path');
        $src = $this->_input->getOption('src');
        $dockername = $this->_input->getOption('name');
        $dockerport = $this->_input->getOption('port');

        // TODO: What if there are multiple sites? Can we setup multiple PHP containers
        // usage example will be Drupal sites where clearing cache doesn't do all sites
        $this->buildOrImage(
            '../vendor/creode/docker/images/php/7.0',
            'creode/php-apache:7.0',
            $this->_config,
            [   // builds
                '../vendor/creode/docker/images/php/7.0' => 'PHP 7.0',
                '../vendor/creode/docker/images/php/5.6' => 'PHP 5.6',
                '../vendor/creode/docker/images/php/5.3' => 'PHP 5.3'
            ],
            [   // images
                'creode/php-apache:7.0' => 'PHP 7.0',
                'creode/php-apache:5.6' => 'PHP 5.6',
                'creode/php-apache:5.3' => 'PHP 5.3'
            ]
        );

        $this->_config['container_name'] = $dockername . '_php';

        $this->_config['ports'] = ['3' . $dockerport . ':80'];

        $this->_config['environment']['VIRTUAL_HOST'] = '.' . $dockername . '.docker';

        $this->_config['links'] = []; 

        $this->_config['volumes'] = ['../' . $src . ':/var/www/html'];
    }
}

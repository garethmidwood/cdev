<?php
namespace Creode\Environment\Docker\Command\Container;

use Symfony\Component\Filesystem\Filesystem;

class Php extends Container
{
    const COMMAND_NAME = 'container:php:configure';
    const COMMAND_DESC = 'Configures the PHP container';
    const CONFIG_FILE = 'php.yml';
    const CONFIG_NODE = 'php';
    const DB_DIR = 'db';

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

    public function __construct(Filesystem $fs)
    {
        $this->_fs = $fs;

        parent::__construct();
    }

    protected function askQuestions()
    {
        $path = $this->_input->getOption('path');
        $config = $this->_input->getOption('config');
        $src = $this->_input->getOption('src');
        $dockername = $this->_input->getOption('name');
        $dockerport = $this->_input->getOption('port');

        if (!$this->_fs->exists($path . '/' . self::DB_DIR)) {
            $this->_fs->mkdir($path . '/' . self::DB_DIR, 0740);
        }

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

        $config['container_name'] = $dockername . '_php';

        $config['ports'] = ['3' . $dockerport . ':80'];

        $config['environment']['VIRTUAL_HOST'] = '.' . $dockername . '.docker';

        $config['links'] = []; 

        $config['volumes'] = ['../' . $src . ':/var/www/html'];
    }
}

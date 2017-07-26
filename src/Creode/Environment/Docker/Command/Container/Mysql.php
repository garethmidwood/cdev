<?php
namespace Creode\Environment\Docker\Command\Container;

use Symfony\Component\Filesystem\Filesystem;

class Mysql extends Container
{
    const COMMAND_NAME = 'container:mysql:configure';
    const COMMAND_DESC = 'Configures the MySQL container';
    const CONFIG_FILE = 'mysql.yml';
    const CONFIG_NODE = 'mysql';
    const DB_DIR = 'db';

    protected $_config = 
    [
        'active' => true,
        'container_name' => 'project_mysql',
        'restart' => 'always',
        'ports' => [
            '3306:3306'
        ],
        'environment' => [
            'MYSQL_ROOT_PASSWORD' => 'root',
            'MYSQL_DATABASE' => 'website',
            'MYSQL_USER' => 'webuser',
            'MYSQL_PASSWORD' => 'webpassword'
        ],
        'volumes' => [
            '../db:/docker-entrypoint-initdb.d',
            '/var/lib/mysql',
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
        $dockername = $this->_input->getOption('name');
        $dockerport = $this->_input->getOption('port');

        if (!$this->_fs->exists($path . '/' . self::DB_DIR)) {
            $this->_fs->mkdir($path . '/' . self::DB_DIR, 0740);
        }

        $this->buildOrImage(
            '../vendor/creode/docker/images/mysql',
            'creode/mysql:5.6',
            $this->_config,
            [   // builds
                '../vendor/creode/docker/images/mysql' => 'MySQL'
            ],
            [   // images
                'creode/mysql:5.6' => 'MySQL'
            ]
        );

        $config['container_name'] = $dockername . '_mysql';

        $config['ports'] = ['4' . $dockerport . ':3306'];

        return $config;
    }
}

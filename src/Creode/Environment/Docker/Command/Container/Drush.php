<?php
namespace Creode\Environment\Docker\Command\Container;

class Drush extends Container
{
    const COMMAND_NAME = 'container:drush:configure';
    const COMMAND_DESC = 'Configures the Drush container';
    const CONFIG_FILE = 'drush.yml';
    const CONFIG_NODE = 'drush';

    protected $_config = 
    [
        'active' => false,
        'container_name' => 'project_drush',
        'image' => 'drupaldocker/drush',
        'links' => [
            'mysql'
        ],
        'volumes_from' => [
            'php'
        ]
    ];

    protected function askQuestions()
    {
        $path = $this->_input->getOption('path');
        $src = $this->_input->getOption('src');
        $dockername = $this->_input->getOption('name');
        $dockerport = $this->_input->getOption('port');

        $this->_config['container_name'] = $dockername . '_drush';
    }
}

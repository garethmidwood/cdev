<?php
namespace Creode\Environment\Docker\Command\Container;

class Redis extends Container
{
    const COMMAND_NAME = 'container:redis:configure';
    const COMMAND_DESC = 'Configures the Redis container';
    const CONFIG_FILE = 'redis.yml';
    const CONFIG_NODE = 'redis';

    protected $_config = 
    [
        'active' => true,
        'image' => 'redis',
        'container_name' => 'project_redis',
        'ports' => [
            '6379'
        ]
    ];

    protected function askQuestions()
    {
        $path = $this->_input->getOption('path');
        $dockername = $this->_input->getOption('name');
        $dockerport = $this->_input->getOption('port');

        $this->_config['container_name'] = $dockername . '_redis';
    }
}

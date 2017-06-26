<?php
namespace Creode\System\Command\Ssh;

use Creode\Cdev\Config;
use hexpang\Client\SSHClient\SSHClient;

class Factory
{
    /**
     * Creates an SSH Client
     * 
     * @param string $host 
     * @param int $port 
     * @param string $user 
     * @param string $pass 
     * @return type
     */
    public static function create($host, $port, $user, $pass)
    {
        return new SSHClient($host, $port, $user, $pass);
    }
}

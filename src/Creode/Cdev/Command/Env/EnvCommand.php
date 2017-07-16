<?php
namespace Creode\Cdev\Command\Env;

use Symfony\Component\Console\Command\Command;
use Creode\Environment\Environment;

abstract class EnvCommand extends Command
{
    /**
     * @var Environment
     */
    protected $_environment;

    /**
     * Constructor
     * @param Environment $environment
     * @return null
     */
    public function __construct(
        Environment $environment
    ) {
        $this->_environment = $environment;

        parent::__construct();
    }
}

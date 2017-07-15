<?php
namespace Creode\Cdev\Command\Env;

use Symfony\Component\Console\Command\Command;
use Creode\Environments\Environment;
use Creode\Framework\Framework;

abstract class EnvCommand extends Command
{
    /**
     * @var Environment
     */
    protected $_environment;

    /**
     * @var Framework
     */
    protected $_framework;

    /**
     * Constructor
     * @param Environment $environment
     * @param Framework $framework 
     * @return null
     */
    public function __construct(
        Environment $environment,
        Framework $framework
    ) {
        $this->_environment = $environment;
        $this->_framework = $framework;

        parent::__construct();
    }
}

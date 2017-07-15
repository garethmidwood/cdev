<?php
namespace Creode\Cdev\Command\Site;

use Symfony\Component\Console\Command\Command;
use Creode\Environment\Environment;

abstract class SiteCommand extends Command
{
    /**
     * @var Environment
     */
    protected $_environment;

    /**
     * Constructor
     * @param Environment $environment
     * @param Framework $framework 
     * @return null
     */
    public function __construct(
        Environment $environment
    ) {
        $this->_environment = $environment;

        parent::__construct();
    }
}

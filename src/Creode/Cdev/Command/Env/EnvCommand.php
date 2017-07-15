<?php
namespace Creode\Cdev\Command\Env;

use Symfony\Component\Console\Command\Command;
use Creode\Tools\ToolInterface;

abstract class EnvCommand extends Command
{
    /**
     * @var ToolInterface
     */
    protected $_tool;

    /**
     * Constructor
     * @param ToolInterface $tool 
     * @return null
     */
    public function __construct(
        ToolInterface $tool
    ) {
        $this->_tool = $tool;

        parent::__construct();
    }
}

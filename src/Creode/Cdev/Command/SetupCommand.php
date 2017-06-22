<?php
namespace Creode\Cdev\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Creode\Tools\ToolInterface as ToolInterface;

class SetupCommand extends Command
{
    /**
     * @var ToolInterface
     */
    private $_tool;

    /**
     * Constructor
     * @param ToolInterface $tool 
     * @return null
     */
    public function __construct(ToolInterface $tool)
    {
        $this->_tool = $tool;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('setup');
        $this->setDescription('Sets up the project to run on a virtual environment');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );

        $this->addOption(
            'src',
            's',
            InputOption::VALUE_REQUIRED,
            'The name of the src directory to use',
            'src'
        );

        $this->addOption(
            'oldsrc',
            'o',
            InputOption::VALUE_OPTIONAL,
            'If entered, the named directory will be renamed to the value of src',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            $this->_tool->setup($input)
        );
    }
}

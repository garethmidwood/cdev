<?php
namespace Creode\Cdev\Command\Magento2;

use Creode\Cdev\Command\ToolCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearCommand extends ToolCommand
{
    protected function configure()
    {
        $this->setName('mage2:cache:clear');
        $this->setDescription('Clears cache for Magento 2');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_tool->input($input);
        
        $output->writeln(
            $this->_tool->runCommand(
                [
                    'bin/magento',
                    'cache:clean'
                ]
            )
        );

        $output->writeln(
            $this->_tool->runCommand(
                [
                    'bin/magento',
                    'cache:flush'
                ]
            )
        );
    }
}

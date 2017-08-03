<?php
namespace Creode\Cdev\Command\Site;

use Creode\Cdev\Command\Site\SiteCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheCleanSiteCommand extends SiteCommand
{
    protected function configure()
    {
        $this->setName('site:cache:clean');
        $this->setAliases(['site:cc']);
        $this->setDescription('Clears [& flushes] caches');

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
        $this->_environment->input($input);
        
        $output->writeln(
            $this->_environment->cacheClear()
        );
    }
}

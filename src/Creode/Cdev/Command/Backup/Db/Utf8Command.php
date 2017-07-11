<?php
namespace Creode\Cdev\Command\Backup\Db;

use Creode\System\Iconv\Iconv;
use Creode\Cdev\Command\Backup\Files;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Utf8Command extends Command
{    
    /**
     * @var Iconv
     */
    private $_iconv;

    /**
     * @param Iconv $iconv 
     * @return null
     */
    public function __construct(
        Iconv $iconv
    ) {
        $this->_iconv = $iconv;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('backup:db:utf8');
        $this->setDescription('Converts a DB dump to UTF8');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );

        $this->addOption(
            'from',
            'f',
            InputOption::VALUE_REQUIRED,
            'File format to convert from',
            'ISO-8859-1'
        );

        $this->addOption(
            'input',
            'i',
            InputOption::VALUE_REQUIRED,
            'File to be updated',
            Files::DB_FILE
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cwd = $input->getOption('path');
        $from = $input->getOption('from');
        $file = $input->getOption('input');
        
        echo 'Converting ' . $file . ' to UTF8' . PHP_EOL;

        $this->_iconv->convert($cwd, $file, 'utf-8', $from);
    }
}

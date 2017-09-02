<?php
namespace Creode\Cdev\Command\Backup\Db;

use Creode\Cdev\Command\Backup\Files;
use Creode\Cdev\Config;
use Creode\Framework\Framework;
use Creode\System\String\StringManipulation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CleanseCommand extends Command
{
    /**
     * @var Config
     */
    private $_config;

    /**
     * @var StringManipulation
     */
    private $_strManipulation;

    /**
     * @var Framework
     */
    private $_framework;

    /**
     * @param Config $config 
     * @param StringManipulation $StringManipulation
     * @param Framework $framework
     * @return null
     */
    public function __construct(
        Config $config,
        StringManipulation $StringManipulation,
        Framework $framework
    ) {
        $this->_config = $config;
        $this->_strManipulation = $StringManipulation;
        $this->_framework = $framework;

        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setName('backup:db:cleanse');
        $this->setDescription('Preps the backup for the dev environment (UTF8, removes unnecessary lines etc)');

        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'Path to cdev.yml file. Defaults to the directory the command is run from',
            Config::CONFIG_DIR . Config::CONFIG_FILE
        );

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
        $cwd = $input->getOption('path');
        
        $output->writeln('Cleansing ' . Files::DB_FILE);

        $this->cleanseDB($cwd, Files::DB_FILE);

        return 'Cleanse complete';
    }

    private function cleanseDB($pwd, $filePath)
    {
        $insertTerms = $this->_framework->getDBTableCleanseList();

        if (empty($insertTerms)) {
            return 'No DB tables were specified for deletion for this framework';
        }

        $this->removeInserts($pwd, $filePath, $insertTerms);
    }

    /**
     * Removes insert statements for the specified tables
     * @param string $pwd 
     * @param string $filePath 
     * @param array $terms 
     * @return type
     */
    private function removeInserts($pwd, $filePath, array $terms = [])
    {
        $matches = [];

        foreach ($terms as $term) {
            $matches[] = 'INSERT INTO `' . $term . '`';
        }

        $this->_strManipulation->removeLinesMatching($pwd, $filePath, $matches);
    }
}

<?php
namespace Creode\Cdev\Command\Backup\Db;

use Creode\Cdev\Command\Backup\Files;
use Creode\Cdev\Config;
use Creode\Cdev\Framework\Magento1;
use Creode\Cdev\Framework\Magento2;
use Creode\Cdev\Framework\Drupal7;
use Creode\Cdev\Framework\Drupal8;
use Creode\Cdev\Framework\WordPress;
use Creode\System\Awk\Awk;
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
     * @var Awk
     */
    private $_strrep;

    /**
     * @param Config $config 
     * @return null
     */
    public function __construct(
        Config $config,
        Awk $awk
    ) {
        $this->_config = $config;
        $this->_strrep = $awk;

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
        $framework = $this->_config->get('framework');

        $cwd = $input->getOption('path');
        
        echo 'Cleansing ' . Files::DB_FILE . ' for ' . $framework . PHP_EOL;

        switch($framework) {
            case Magento1::NAME:
                $this->cleanseMagento1($cwd, Files::DB_FILE);
                break;
            case Magento2::NAME:
            case Drupal7::NAME:
            case Drupal8::NAME:
            case WordPress::NAME:
            default:
                echo 'Your framework is not (yet) supported' . PHP_EOL;
                break;
        }

        return 'Cleanse complete';
    }

    private function cleanseMagento1($pwd, $filePath)
    {
        $insertTerms = [
            'adminnotification_inbox',
            'aw_core_logger',
            'dataflow_batch_export',
            'dataflow_batch_import',
            'log_customer',
            'log_quote',
            'log_summary',
            'log_summary_type',
            'log_url',
            'log_url_info',
            'log_visitor',
            'log_visitor_info',
            'log_visitor_online',
            'index_event',
            'report_event',
            'report_viewed_product_index',
            'report_compared_product_index',
            'catalog_compare_item',
            'catalogindex_aggregation',
            'catalogindex_aggregation_tag',
            'catalogindex_aggregation_to_tag',
            'core_session',
            'catalogsearch_result'
        ];

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

        $this->_strrep->removeLinesMatching($pwd, $filePath, $matches);
    }
}

<?php
namespace Creode\Cdev\Command\Backup;

use Creode\Cdev\Config;
use Creode\System\Ssh\Ssh;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

class PullCommand extends Command
{
    /**
     * @var Config
     */
    private $_config;

    /**
     * @var Ssh
     */
    private $_ssh;

    /**
     * @param Config $config 
     * @param Ssh $ssh 
     * @return null
     */
    public function __construct(
        Config $config,
        Ssh $ssh
    ) {
        $this->_config = $config;
        $this->_ssh = $ssh;

        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setName('backup:pull');
        $this->setDescription('Pulls the latest backups');

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
        $answers = $this->askQuestions($input, $output);

        $backupServer = $this->_config->get('backups');
        $path = $input->getOption('path');

        $cwd = $path . '/';
        
        $transfers = [];

        switch($answers['backupType']) {
            case 'all':
                $transfers[] = [
                    'target' => $cwd . Files::MEDIA_FILE,
                    'source' => $backupServer['media-dir'] . $backupServer['media-file']
                ];
                $transfers[] = [
                    'target' => $cwd . Files::DB_FILE,
                    'source' => $backupServer['db-dir'] . $backupServer['db-file']
                ];
                break;
            case 'media':
                $transfers[] = [
                    'target' => $cwd . Files::MEDIA_FILE,
                    'source' => $backupServer['media-dir'] . $backupServer['media-file']
                ];
                break;
            case 'database':
            default:
                $transfers[] = [
                    'target' => $cwd . Files::DB_FILE,
                    'source' => $backupServer['db-dir'] . $backupServer['db-file']
                ];
                break;
        }

        foreach ($transfers as $transfer) {
            $this->_ssh->download(
                $path,
                'backups',
                $transfer['source'],
                $transfer['target'],
                $output
            );
        }
    }

    private function askQuestions(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Backup(s) to retrieve',
            array(
                'database',
                'media',
                'all'
            )
        );
        $question->setErrorMessage('Backup %s is invalid.');

        $answers['backupType'] = $helper->ask($input, $output, $question);

        return $answers;
    }
}

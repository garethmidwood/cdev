<?php
namespace Creode\Cdev\Command\Cdev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class ConfigureCommand extends Command
{
    const CONFIG_FILE = 'cdev.yml';

    private $_config = array(
        'version' => '2',
        'config' => array(
            'backups' => array(
                'user' => null,
                'pass' => null,
                'host' => null,
                'port' => null,
                'db-dir' => null,
                'db-file' => null,
                'media-dir' => null,
                'media-file' => null,
            )
        )
    );

    protected function configure()
    {
        $this->setName('configure');
        $this->setDescription('Creates this tools configuration file for the repo');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to run commands on. Defaults to the directory the command is run from',
            getcwd()
        );

        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'Config file to create',
            self::CONFIG_FILE
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $answers = $this->askQuestions($input, $output);

        $output->writeln('Writing config file to ' . $input->getOption('path') . '/' . $input->getOption('config'));

        $configuration = Yaml::dump($this->_config);

        file_put_contents(
            $input->getOption('path') . '/' . $input->getOption('config'),
            $configuration
        );
    }

    private function askQuestions(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $question = new Question('Backups: Host (defaults to NAS setting) ', '192.168.0.97');
        $this->_config['config']['backups']['host'] = $helper->ask($input, $output, $question);

        $question = new Question('Backups: Port (defaults to NAS setting) ', '22');
        $this->_config['config']['backups']['port'] = $helper->ask($input, $output, $question);

        $question = new Question('Backups: User (defaults to NAS setting) ', 'creode');
        $this->_config['config']['backups']['user'] = $helper->ask($input, $output, $question);

        $question = new Question('Backups: Password');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || strlen($answer) == 0) {
                throw new \RuntimeException(
                    'You must enter a password'
                );
            }

            return $answer;
        });
        $this->_config['config']['backups']['pass'] = $helper->ask($input, $output, $question);

        $question = new Question('Backups: DB Directory (e.g. /var/services/homes/creode/clients/{client}/database/) ');
        $this->_config['config']['backups']['db-dir'] = $helper->ask($input, $output, $question);

        $question = new Question('Backups: DB file name (defaults to weekly-backup.sql) ', 'weekly-backup.sql');
        $this->_config['config']['backups']['db-file'] = $helper->ask($input, $output, $question);

        $question = new Question('Backups: Media Directory (e.g. /var/services/homes/creode/clients/{client}/media/) ');
        $this->_config['config']['backups']['media-dir'] = $helper->ask($input, $output, $question);

        $question = new Question('Backups: Media file name (defaults to weekly-backup.tar) ', 'weekly-backup.tar');
        $this->_config['config']['backups']['media-file'] = $helper->ask($input, $output, $question);

    }
}

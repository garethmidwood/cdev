<?php
namespace Creode\Cdev\Command\Cdev;

use Creode\Cdev\Framework\Magento1;
use Creode\Cdev\Framework\Magento2;
use Creode\Cdev\Framework\Drupal7;
use Creode\Cdev\Framework\Drupal8;
use Creode\Cdev\Framework\WordPress;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Yaml\Yaml;

class ConfigureCommand extends Command
{
    const CONFIG_FILE = 'cdev.yml';

    private $_config = array(
        'version' => '2',
        'config' => array(
            'framework' => null,
            'backups' => array(
                'user' => 'creode',
                'host' => '192.168.0.97',
                'port' => '22',
                'db-dir' => 'e.g. /var/services/homes/creode/clients/{client}/database/',
                'db-file' => 'weekly-backup.sql',
                'media-dir' => 'e.g. /var/services/homes/creode/clients/{client}/media/',
                'media-file' => 'weekly-backup.tar',
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
        $configFile = $input->getOption('path') . '/' . $input->getOption('config');

        if (file_exists($configFile)) {
            $this->_config = Yaml::parse(file_get_contents($configFile));
        }

        $answers = $this->askQuestions($input, $output);

        $output->writeln('Writing config file to ' . $input->getOption('path') . '/' . $input->getOption('config'));

        $configuration = Yaml::dump($this->_config);

        file_put_contents(
            $configFile,
            $configuration
        );
    }

    private function askQuestions(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Framework',
            array(
                Magento1::NAME,
                Magento2::NAME,
                Drupal7::NAME,
                Drupal8::NAME,
                WordPress::NAME
            )
        );
        $question->setErrorMessage('Framework %s is invalid.');
        $this->_config['config']['framework'] = $helper->ask($input, $output, $question);

        $this->askQuestion(
            'Backups: Host',
            $this->_config['config']['backups']['host'],
            $input, 
            $output
        );

        $this->askQuestion(
            'Backups: Port',
            $this->_config['config']['backups']['port'],
            $input, 
            $output
        );

        $this->askQuestion(
            'Backups: User',
            $this->_config['config']['backups']['user'],
            $input, 
            $output
        );

        $this->askQuestion(
            'Backups: DB Directory',
            $this->_config['config']['backups']['db-dir'],
            $input, 
            $output
        );

        $this->askQuestion(
            'Backups: DB file name',
            $this->_config['config']['backups']['db-file'],
            $input, 
            $output
        );

        $this->askQuestion(
            'Backups: Media Directory',
            $this->_config['config']['backups']['media-dir'],
            $input, 
            $output
        );

        $this->askQuestion(
            'Backups: Media file name',
            $this->_config['config']['backups']['media-file'],
            $input, 
            $output
        );
    }

    private function askQuestion($text, &$config, $input, $output) 
    {
        $helper = $this->getHelper('question');

        $question = new Question(
            $text . ' : [Current=' . $config . ']',
            $config
        );

        $config = $helper->ask($input, $output, $question);
    }


}

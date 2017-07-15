<?php
namespace Creode\Cdev\Command\Cdev;

use Creode\Cdev\Config;
use Creode\Environment;
use Creode\Framework;
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
    private $_config = array(
        'version' => '2',
        'config' => array(
            'environment' => array(
                'type' => 'docker',
                'framework' => null
            ),
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
        $this->setDescription('Creates this environments configuration file');

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
            Config::CONFIG_FILE
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configDir = $input->getOption('path') . '/' . Config::CONFIG_DIR;

        if (!file_exists($configDir)) {
            mkdir($configDir, 0744);
        }

        $configFile = $configDir . $input->getOption('config');
        $servicesFile = $configDir . 'services.env.xml';

        if (file_exists($configFile)) {
            $this->_config = Yaml::parse(file_get_contents($configFile));
        }

        $answers = $this->askQuestions($input, $output);

        $output->writeln('Writing config file to ' . $configFile);

        $configuration = Yaml::dump($this->_config);

        file_put_contents(
            $configFile,
            $configuration
        );

        $this->saveServicesXml($servicesFile);
    }

    private function askQuestions(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Environment type',
            array(
                // TODO: Find a better way to include these, ideally by adding the classes somehow
                Environment\Docker\Docker::NAME
            )
        );
        $question->setErrorMessage('Environment type %s is invalid.');
        $this->_config['config']['environment']['type'] = $helper->ask($input, $output, $question);

        $question = new ChoiceQuestion(
            'Framework',
            // TODO: Find a better way to include these, ideally by adding the classes somehow
            array(
                'magento1',
                'magento2',
                'drupal7',
                'drupal8',
                'wordpress'
            )
            // array(
            //     Framework\Magento1\Magento1::NAME,
            //     Framework\Magento2\Magento2::NAME,
            //     Framework\Drupal7\Drupal7::NAME,
            //     Framework\Drupal8\Drupal8::NAME,
            //     Framework\WordPress\WordPress::NAME
            // )
        );
        $question->setErrorMessage('Framework %s is invalid.');
        $this->_config['config']['environment']['framework'] = $helper->ask($input, $output, $question);

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

    /**
     * Saves project specific service XML based on provided template
     * @param string $servicesFile 
     * @return null
     */
    private function saveServicesXml($servicesFile)
    {
        $servicesContent = file_get_contents(__DIR__ . '/../../../../templates/services.env.xml');
        
        $searches = [
            '{{env_type}}',
            '{{env_framework}}'
        ];

        $replacements = [
            $this->_config['config']['environment']['type'],
            $this->_config['config']['environment']['framework']
        ];

        $servicesContent = str_replace($searches, $replacements, $servicesContent);

        file_put_contents(
            $servicesFile,
            $servicesContent
        );
    }


}

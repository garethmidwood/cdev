<?php
namespace Creode\Cdev\Command\Env;

use Creode\Cdev\Command\Env\EnvCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SetupEnvCommand extends EnvCommand
{
    protected function configure()
    {
        $this->setName('env:setup');
        $this->setDescription('Sets up the project environment');

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

        $this->addOption(
            'composer',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Path to composer executable',
            '/usr/local/bin/composer.phar'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $answers = $this->askQuestions($input, $output);

        $this->_tool->input($input);

        $output->writeln(
            $this->_tool->setup($answers)
        );
    }

    private function askQuestions(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // TODO: This is tool-specific. Find a way to make it so.

        $question = new Question('Package name (<vendor>/<name>) ', 'creode/toolazytotype');
        $answers['packageName'] = $helper->ask($input, $output, $question);

        $question = new Question('Environment port suffix (3 digits - e.g. 014) ', 'XXX');
        $answers['portNo'] = $helper->ask($input, $output, $question);

        $question = new Question('Project name (xxxx).docker ', 'toolazytotype');
        $answers['projectName'] = $helper->ask($input, $output, $question);

        return $answers;
    }
}

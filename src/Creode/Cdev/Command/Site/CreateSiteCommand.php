<?php
namespace Creode\Cdev\Command\Site;

use Creode\Cdev\Command\ConfigurationCommand;
use Creode\Cdev\Config;
use Creode\Collections\FrameworkCollection;
use Creode\System\Git\Git;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CreateSiteCommand extends ConfigurationCommand
{
    /**
     * @var array
     */
    private $_frameworks;

    /**
     * @var Filesystem
     */
    protected $_fs;

    /**
     * @var Finder
     */
    protected $_finder;

    /**
     * @var Git
     */
    protected $_git;

    /**
     * Constructor
     * @param Filesystem $fs
     * @param Finder $finder
     * @return null
     */
    public function __construct(
        Filesystem $fs,
        Finder $finder,
        Git $git,
        FrameworkCollection $frameworkCollection
    ) {
        $this->_fs = $fs;
        $this->_finder = $finder;
        $this->_git = $git;
        $this->_frameworks = $frameworkCollection->getItems();

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('site:create');
        $this->setDescription('Creates a new site from boilerplate');

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
        $this->_input = $input;
        $this->_output = $output;

        $path = $this->_input->getOption('path');

        $this->loadConfig(Config::getGlobalConfigDir(), Config::CONFIG_FILE, $output);

        $this->askQuestions();

        // $this->createSite($output);
    }

    /**
     * Asks the user questions
     * @return null
     */
    private function askQuestions()
    {
        $helper = $this->getHelper('question');
        $path = $this->_input->getOption('path');

        /**
         * 
         *  Choose directory to create
         * 
         */      
        $dirName = null;

        $this->askQuestion(
            'Directory name (leave empty to automatically generate)',
            $dirName
        );

        /**
         * 
         *  Choose framework
         * 
         */
        $frameworks = [];
        $frameworkMap = [];
        foreach ($this->_frameworks as $framework) {
            $frameworks[$framework::NAME] = $framework::LABEL;
            $frameworkMap[$framework::NAME] = $framework;
        }

        $question = new ChoiceQuestion(
            '<question>Framework</question>',
            $frameworks
        );
        $question->setErrorMessage('Framework %s is invalid.');
        $framework = $helper->ask($this->_input, $this->_output, $question);

        /**
         * 
         *  Choose boilerplate
         * 
         */
        $boilerplates = isset($this->_config['config']['boilerplates'][$framework]['boilerplates'])
                    ?   $this->_config['config']['boilerplates'][$framework]['boilerplates']
                    :   false;

        if (!$boilerplates || empty($boilerplates)) {
            $this->_output->writeln('<warning>No boilerplates found. Run global:configure to add repositories</warning>');
            return;
        }

        $bpChoices = $bpMap = [];
        foreach ($boilerplates as $key => $bp) {
            $repo = is_string($bp)
                ? ['repo' => $bp, 'branch' => 'master']
                : $bp;

            $repoName = $repo['repo'] . ' [' . $repo['branch'] . ']';

            $bpChoices[$key] = $repoName;
            $bpMap[$repoName] = $repo;
        }

        $question = new ChoiceQuestion(
            '<question>Boilerplate</question>',
            $bpChoices
        );
        $question->setErrorMessage('Boilerplate %s is invalid.');
        $chosenBp = $helper->ask($this->_input, $this->_output, $question);

        $chosenBoilerplate = $bpMap[$chosenBp];

        /**
         * 
         *  Clone repository
         * 
         */
        $this->_git->cloneRepo($path, $chosenBoilerplate['repo'], $chosenBoilerplate['branch'], $dirName);

        /**
         * 
         *  Detach from origin repository and remove history
         * 
         */
        $repoPath = $path . '/' . $dirName;

        $this->_git->removeRepository($repoPath);

        /**
         * 
         *  Initialise new repository
         * 
         */
        $this->_git->init($repoPath);
        $this->_git->add($repoPath);
        $this->_git->commit($repoPath, 'Initial import from boilerplate using cdev');
    }
}

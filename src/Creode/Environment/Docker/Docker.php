<?php

namespace Creode\Environment\Docker;

use Creode\Cdev\Config;
use Creode\Environment\Docker\System\Compose\Compose;
use Creode\Environment\Docker\System\Docker as SystemDocker;
use Creode\Environment\Docker\System\Sync\Sync;
use Creode\Environment\Environment;
use Creode\Framework\Framework;
use Creode\System\Composer\Composer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;



class Docker extends Environment
{
    const NAME = 'docker';
    const LABEL = 'Docker';
    const COMMAND_NAMESPACE = 'docker';
    
    /**
     * @var SystemDocker
     */
    private $_docker;

    /**
     * @var Compose
     */
    private $_compose;

    /**
     * @var Sync
     */
    private $_sync;

    /**
     * @var Framework
     */
    private $_framework;

    /**
     * @var Composer
     */
    private $_composer;

    /**
     * @var ConsoleLogger
     */
    private $_logger;

    /**
     * @var Filesystem
     */
    private $_fs;

    /**
     *  @var Finder
     */
    private $_finder;

    /**
     *  @var InputInterface
     */
    private $_input;

    /**
     * @param Compose $compose 
     * @param Sync $sync 
     * @param Filesystem $fs 
     * @param Finder $finder
     * @return null
     */
    public function __construct(
        SystemDocker $docker,
        Compose $compose,
        Sync $sync,
        Framework $framework,
        Composer $composer,
        Filesystem $fs,
        Finder $finder
    ) {
        $this->_docker = $docker;
        $this->_compose = $compose;
        $this->_sync = $sync;
        $this->_framework = $framework;
        $this->_composer = $composer;
        $this->_fs = $fs;
        $this->_finder = $finder;
    }

    /**
     * Sets the inputs
     * @param InputInterface $input 
     * @return type
     */
    public function input(InputInterface $input)
    {
        $this->_input = $input;
    }

    /**
     * Sets up the directory with docker 
     * @param InputInterface $input 
     * @return null
     */
    public function setup(array $answers = array())
    {
        $this->validateAnswers($answers);

        $this->_answers = $answers;

        $oldSrc = $this->_input->getOption('oldsrc');

        if (isset($oldSrc)) 
        {
            $this->renameSrcDir();
        } else {
            $this->createSrcDir();
            $this->moveFilesToSrc();
        }

        $this->composerSetPath(
            $this->_input->getOption('composer')
        );

        $this->composerInit();

        $this->composerInstall();

        $this->configureDocker();
    }

    public function start()
    {
        $this->logTitle('Starting dev environment...');

        $path = $this->_input->getOption('path');
        $build = $this->_input->getOption('build');

        $this->_sync->start($path);
        $this->_compose->up($path, $build);
    }

    public function stop()
    {
        $this->logTitle('Stopping dev environment...');

        $path = $this->_input->getOption('path');

        $this->_compose->stop($path);
    }

    public function nuke()
    {
        $this->logTitle('Nuking dev environment...');

        $path = $this->_input->getOption('path');

        $this->_compose->stop($path);
        $this->_compose->rm($path);
        $this->_sync->clean($path);
    }

    public function cleanup()
    {
        $this->logTitle('Cleaning up Docker leftovers...');

        $path = $this->_input->getOption('path');

        $this->_docker->cleanup($path);
    }

    public function ssh()
    {
        $this->logTitle('Connecting to server...');

        $path = $this->_input->getOption('path');
        $user = $this->_input->getOption('user');

        $this->logMessage("Connecting as $user");

        $this->_compose->ssh($path, $user);
    }

    public function cacheClear()
    {
        $commands = $this->_framework->clearCache();

        foreach ($commands as $command) {
            $this->runCommand($command);
        }
    }

    /**
     * Runs a command on the docker-compose php container
     * @param array $command 
     * @param bool $elevatePermissions 
     * @return null
     */
    public function runCommand(array $command = array(), $elevatePermissions = false)
    {
        $path = $this->_input->getOption('path');

        $command = array_merge(
            [
                'exec',
                '--user=' . ($elevatePermissions ? 'root' : 'www-data'),
                'php'
            ],
            $command
        );
        
        $this->_compose->runCmd(
            $path,
            $command
        );
    }

    private function validateAnswers(array $answers = array())
    {
        $messages = array();

        if (!isset($answers['packageName'])) {
            $messages[] = 'Composer package name must be filled in';
        }

        if (!isset($answers['projectName'])) {
            $messages[] = 'Docker project name must be filled in';
        } elseif (!filter_var('http://'.$answers['projectName'].'.com', FILTER_VALIDATE_URL)) {
            $messages[] = 'Docker project name must be suitable for use in domain name (no spaces, underscores etc.)';
        }

        if (!isset($answers['portNo'])) {
            $messages[] = 'Docker port number must be filled in';
        } elseif (!preg_match('/^[0-9]{3}$/',$answers['portNo'])) {
            $messages[] = 'Docker port number must be a 3 digit number';
        }

        if (count($messages) > 0) {
            foreach ($messages as $message) {
                $this->logError($message);
            }
            throw new \Exception('Validation issues with input data. See log for details');
        }
    }



    /**
     * Sets composer executable path
     * @param string $composerPath Path to composer executable
     * @return null
     */
    private function composerSetPath($composerPath)
    {
        $this->_composer->setPath($composerPath);
    }

    private function composerInit()
    {
        $this->logTitle('Initialising composer');

        $path = $this->_input->getOption('path');

        if ($this->_fs->exists($path . '/composer.json')) {
            $this->logNotice('composer.json already exists, skipping');
            return;
        }

        $this->logMessage(
            $this->_composer->init($path, $this->_answers['packageName'])
        );
    }

    private function composerInstall()
    {
        $this->logTitle('Running composer install');

        $path = $this->_input->getOption('path');

        $this->logMessage(
            $this->_composer->install($path)
        );
    }

    private function configureDocker()
    {
        $this->logTitle('Copying Docker templates');

        foreach (Repo::TEMPLATES as $dockerTemplate) {
            $this->tailorDockerTemplate(
                $this->copyDockerTemplate($dockerTemplate)
            );
        }
    }

    private function copyDockerTemplate($dockerTemplate)
    {
        $this->logMessage("Copying $dockerTemplate to project directory");

        $path = $this->_input->getOption('path');

        $templatePath = $path . Repo::TEMPLATE_PATH . $dockerTemplate;

        if (!$this->_fs->exists($templatePath)) {
            $this->logError("$templatePath doesn't exist. Aborting");
            throw new \Exception("$templatePath doesn't exist");
        }

        $newTemplatePath = $path . '/' . $dockerTemplate;

        $this->_fs->copy(
            $templatePath,
            $newTemplatePath,
            true
        );

        return $newTemplatePath;
    }

    private function tailorDockerTemplate($template)
    {
        $config = Yaml::parse(file_get_contents($template));

        // replace docker sync volume names
        if (isset($config['volumes'])) {
            foreach ($config['volumes'] as $key => $service) {
                $this->logMessage("Replacing $key sync volume name");
                $config['volumes'][$this->_answers['projectName'] . '-' . $key] = $service;
                unset($config['volumes'][$key]);
            }
        }

        if (isset($config['syncs'])) {
            foreach ($config['syncs'] as $key => $service) {
                $this->logMessage("Replacing $key sync volume name");
                $config['syncs'][$this->_answers['projectName'] . '-' . $key] = $service;
                unset($config['syncs'][$key]);
            }
        }

        // replace files for services in docker-compose.yml
        if (isset($config['services'])) {
            foreach ($config['services'] as $key => &$service) {
                if (isset($service['ports'])) {
                    foreach($service['ports'] as &$ports) {
                        $this->logMessage("Replacing ports for $key [$ports]");
                        $ports = str_replace('001:', $this->_answers['portNo'] . ':', $ports);
                        $this->logMessage(" New value $ports");
                    }
                }

                if (isset($service['container_name'])) {
                    $this->logMessage("Replacing container name for $key");
                    $service['container_name'] = str_replace('yourproject', $this->_answers['projectName'], $service['container_name']);
                    $this->logMessage(" New value {$service['container_name']}");
                }

                if ($key == 'php' && isset($service['environment'])) {
                    $this->logMessage("Replacing domain name for $key");
                    $service['environment'] = str_replace('yourproject', $this->_answers['projectName'], $service['environment']);
                    $domainName = str_replace('VIRTUAL_HOST=', '*', $service['environment']);
                    $this->logMessage(" New value {$domainName[0]}");
                }

                if (isset($service['volumes'])) {
                    $this->logMessage("Replacing sync volume names for $key");
                    $service['volumes'] = str_replace('website-code-sync:', $this->_answers['projectName'] . '-website-code-sync:', $service['volumes']);
                    $this->logMessage(" New value {$this->_answers['projectName']}-website-code-sync");
                }
            } 
        } 

        $updatedConfig = Yaml::dump($config);

        file_put_contents($template, $updatedConfig);
    }

    /**
     * Returns docker compose system object
     * @return Compose
     */
    public function getCompose()
    {
        return $this->_compose;
    }

    /**
     * Returns docker sync system object
     * @return Sync
     */
    public function getSync()
    {
        return $this->_sync;
    }
}

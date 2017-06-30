<?php

namespace Creode\Tools\Docker;

use Creode\Tools\ToolInterface;
use Creode\Tools\Logger;
use Creode\System\Command\Docker\Compose;
use Creode\System\Command\Docker\Sync;
use Creode\System\Command\Composer\Composer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

class Docker extends Logger implements ToolInterface
{
    const CONFIG_FILE = 'cdev.yml';
    
    /**
     * @var Compose
     */
    private $_compose;

    /**
     * @var Sync
     */
    private $_sync;

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
        Compose $compose,
        Sync $sync,
        Composer $composer,
        Filesystem $fs,
        Finder $finder
    ) {
        $this->_compose = $compose;
        $this->_sync = $sync;
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

        $this->_sync->start($path);
        $this->_compose->up($path);
    }

    public function stop()
    {
        $this->logTitle('Stopping dev environment...');

        $path = $this->_input->getOption('path');

        $this->_compose->stop($path);
        $this->_sync->stop($path);
    }

    public function nuke()
    {
        $this->logTitle('Nuking dev environment...');

        $path = $this->_input->getOption('path');

        $this->_compose->rm($path);
        $this->_sync->clean($path);
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

    private function renameSrcDir()
    {
        $this->logTitle('Renaming src directory');

        $path = $this->_input->getOption('path');
        $oldSrc = $this->_input->getOption('oldsrc');
        $src = $this->_input->getOption('src');

        $oldSrcPath = $path . '/' . $oldSrc;
        $srcPath = $path . '/' . $src;

        if (!$this->_fs->exists($oldSrcPath))
        {
            $this->logError("$oldSrc directory doesn't exist. Aborting");
            throw new \Exception("$oldSrc directory doesn't exist");
        }
            
        $this->logMessage("Renaming $oldSrc directory to $src");

        $this->_fs->rename(
            $oldSrcPath,
            $srcPath 
        );
    }

    private function createSrcDir()
    {
        $this->logTitle('Creating src directory');

        $path = $this->_input->getOption('path');
        $src = $this->_input->getOption('src');

        $srcPath = $path . '/' . $src;

        if ($this->_fs->exists($srcPath))
        {
            $this->logNotice("$src directory already exists. Continuing with existing dir");
            return;
        }
            
        $this->logMessage("Creating $src directory");
        
        $this->_fs->mkdir($srcPath, 0740);

        $this->logMessage("$src directory created");
    }

    private function moveFilesToSrc()
    {
        $this->logTitle('Moving files to src directory');

        $path = $this->_input->getOption('path');
        $src = $this->_input->getOption('src');

        $this->_finder
            ->in($path)
            ->depth('== 0')
            ->exclude($src)
            ->exclude(self::CONFIG_FILE);

        foreach (Repo::TEMPLATES as $dockerTemplate) {
            if ($this->_fs->exists($path . '/' . $dockerTemplate)) {
                $this->logError("$dockerTemplate exists. Project is already set up?");
                throw new \Exception("$dockerTemplate exists. Project is already set up?");
            }
        }

        if (count($this->_finder) == 0) {
            $this->logNotice("No files to move");
            return;
        }

        foreach ($this->_finder as $file) {
            $this->logMessage("Moving {$file->getFileName()} into $src directory");

            $this->_fs->rename(
                $file->getPath() . '/' . $file->getFileName(),
                $file->getPath() . '/' . $src . '/' . $file->getFileName() 
            );
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
        if (!isset($config['services'])) {
            return;
        }

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

        $updatedConfig = Yaml::dump($config);

        file_put_contents($template, $updatedConfig);
    }
}

<?php
namespace Creode\System\Composer;

use Creode\System\Command;

class Composer extends Command
{
    /**
     * Composer executable path
     * @var string
     */
    private $_composer = 'php /usr/local/bin/composer.phar';

    // TODO: The composer path should be set in global config and this functon used to set it
    public function setPath($path)
    {
        $this->_composer = $path;
    }

    /**
     * Returns true if the given path is already initialised for composer
     * @param string $path 
     * @return boolean
     */
    public function isInitialised($path)
    {
        return file_exists($path . DIRECTORY_SEPARATOR . 'composer.json');
    }

    /**
     * initiliases new repo
     * @param string $path 
     * @param string $packageName 
     * @param array $additionalParams 
     * @return string
     */
    public function init($path, $packageName, $additionalParams = array())
    {
        $params = array_merge(
            [
                'init',
                '-n',
                '--name', $packageName,
                '--stability', 'dev',
            ],
            $additionalParams
        );

        $this->runExternalCommand(
            $this->_composer,
            $params,
            $path
        );

        return 'composer init completed';
    }

    /**
     * Installs packages from composer.json
     * @param string $path 
     * @return string
     */
    public function install($path)
    {
        $this->runExternalCommand(
            $this->_composer,
            [
                'install'
            ],
            $path
        );

        return 'composer install completed';
    }

    /**
     * Adds a package
     * @param string $path 
     * @param string|array $package 
     * @return string
     */
    public function require($path, $package)
    {
        $package = is_array($package) ? $package : [$package];
        $params = array_merge(['require'], $package);

        $this->runExternalCommand(
            $this->_composer,
            $params,
            $path
        );

        return 'composer require ' . implode(' ', $package) . ' completed';
    }    

    /**
     * Searches packages
     * @param string $path 
     * @param string|array $package 
     * @param array $parameters
     * @return string
     */
    public function search($path, $package, $parameters = [])
    {
        $packages = is_array($package) ? $package : [$package];
        $params = array_merge(['search'], $packages, $parameters);

        $this->runExternalCommand(
            $this->_composer,
            $params,
            $path
        );

        return 'composer search ' . implode(' ', $params) . ' completed';
    }

    /**
     * Updates a package
     * @param string $path 
     * @param string $package 
     * @return string
     */
    public function update($path, $package)
    {
        $this->runExternalCommand(
            $this->_composer,
            [
                'update',
                $package
            ],
            $path
        );

        return 'composer update ' . $package . ' completed';
    }

    /**
     * Removes a package
     * @param string $path 
     * @param string $package 
     * @return string
     */
    public function remove($path, $package)
    {
        $this->runExternalCommand(
            $this->_composer,
            [
                'remove',
                $package
            ],
            $path
        );

        return 'composer remove ' . $package . ' completed';
    }

    /**
     * Lists info about the packages
     * @param string $path
     * @return string
     */
    public function info($path)
    {
        $this->runExternalCommand(
            $this->_composer,
            [
                'info'
            ],
            $path
        );

        return 'composer list completed';
    }

}

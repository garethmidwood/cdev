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
     * @param string $package 
     * @return string
     */
    public function require($path, $package)
    {
        $this->runExternalCommand(
            $this->_composer,
            [
                'require',
                $package
            ],
            $path
        );

        return 'composer require ' . $package . ' completed';
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

}

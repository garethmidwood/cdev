<?php

namespace Creode\Framework\Magento2;

use Creode\Framework\Framework;

class Magento2 implements Framework
{
    const NAME = 'magento2';

    public function clearCache()
    {   
        echo 'not done it yet..' . PHP_EOL;
        // $output->writeln(
        //     $this->_tool->runCommand(
        //         [
        //             'bin/magento',
        //             'cache:clean'
        //         ]
        //     )
        // );

        // $output->writeln(
        //     $this->_tool->runCommand(
        //         [
        //             'bin/magento',
        //             'cache:flush'
        //         ]
        //     )
        // );
    }
}

<?php

namespace Creode\Tools;

abstract class Logger
{
    protected function logMessage($message)
    {
        echo ' - ' . $message . PHP_EOL;
    }

    protected function logTitle($title)
    {
        echo PHP_EOL . '>>>>>>>>>> ' . strtoupper($title) . PHP_EOL;
    }

    protected function logError($message)
    {
        echo ' ! ' . $message . PHP_EOL;
    }

    protected function logNotice($message)
    {
        echo ' * ' . $message . PHP_EOL;
    }
}

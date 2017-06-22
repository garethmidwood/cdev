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
        echo '================================' . PHP_EOL;
        echo strtoupper($title) . PHP_EOL;
        echo '================================' . PHP_EOL;
    }
}

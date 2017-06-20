<?php

namespace Creode\Tools;

interface ToolInterface
{
    public function install();
    public function start();
    public function stop();
    public function nuke();
}

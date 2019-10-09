<?php
namespace Dvelum\BackgroundTask\Launcher;
use Dvelum\BackgroundTask\Launcher;

class Simple extends Launcher
{
    public function launch($task , array $config)
    {
        $task = new $task($config);
        exit();
    }
}
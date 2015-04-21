<?php
class Bgtask_Launcher_Simple extends Bgtask_Launcher
{
    public function launch($task , array $config)
    {
        $task = new $task($config);
        exit();
    }
}
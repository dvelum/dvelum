<?php
declare(strict_types=1);

namespace Dvelum\App\Console\Clear;

use Dvelum\App\Console;
use Dvelum\Orm\Model;

class Memory extends Console\Action
{
    public function action(): bool
    {
        $taskModel = Model::factory('Bgtask');
        $signalModel = Model::factory('Bgtask_Signal');
        try {
            $taskModel->getDbConnection()->query('DELETE FROM `' . $taskModel->table() . '` WHERE TO_DAYS(`time_started`)< TO_DAYS (DATE("now"))');
            $signalModel->getDbConnection()->query('DELETE FROM `' . $signalModel->table() . '`');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
<?php
class Console_Clear_Memory extends Console_Action
{
    /**
     * (non-PHPdoc)
     * @see Console_Action::run()
     */
    public function run()
    {
        $taskModel = Model::factory('Bgtask');
        $signalModel = Model::factory('Bgtask_Signal');
        $taskModel->getDbConnection()->query('DELETE FROM `'.$taskModel->table().'` WHERE TO_DAYS(`time_started`)< TO_DAYS (DATE("now"))');
        $signalModel->getDbConnection()->query('DELETE FROM `'.$signalModel->table().'`');
    }
}
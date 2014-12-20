<?php
class Cronjob_Clearmemory extends Cronjob_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see Cronjob_Abstract::run()
	 */
	public function run()
	{
		if(!$this->_checkCanLock())
			return false;

		$time = microtime(true);

        $taskModel = Model::factory('Bgtask');
        $signalModel = Model::factory('Bgtask_Signal');
        $taskModel->getDbConnection()->query('DELETE FROM `'.$taskModel->table().'` WHERE TO_DAYS(`time_started`)< TO_DAYS (DATE("now"))');
        $signalModel->getDbConnection()->query('DELETE FROM `'.$signalModel->table().'`');

        $this->_stat['time'] = number_format((microtime(true)-$time) , 5).'s.';
		return true;
	}
}
<?php
class Model_Bgtask_Signal extends Model
{
	/**
	 * Remove signals for object
	 * @param integer $pid
	 * @return void
	 */
	public function clearSignals($pid){
		$this->_db->delete($this->table() , '`pid` = ' . intval($pid));
	}
}
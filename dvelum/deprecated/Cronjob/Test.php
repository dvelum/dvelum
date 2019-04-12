<?php
class Cronjob_Test extends Cronjob_Abstract
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

		for($i=0;$i<20;$i++)
		{
		    sleep(2);

		    echo $i."\n";
		    // ... do something
			$this->_stat['count'] = $i;
            $this->_stat['time'] = number_format((microtime(true)-$time) , 5).'s.';
            $this->checkTimeLimit();
		}
		return true;
	}
}
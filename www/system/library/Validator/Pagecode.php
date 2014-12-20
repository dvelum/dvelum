<?php
class Validator_Pagecode implements Validator_Interface
{
	/**
     * Validation method
     * @param mixed $value
     * @return boolean
     */
	static public function validate($value)
	{
		return !filter_var($value , FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>"/[^a-z0-9_-]/i")));
	}
}
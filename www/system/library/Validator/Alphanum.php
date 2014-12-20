<?php
class Validator_Alphanum implements Validator_Interface
{
	/**
	 * Validate value
	 * @param string $value
	 * @return boolean
	 */
	static public function validate($value)
	{
		return !filter_var($value , FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>"/[^A-Za-z0-9_]/i")));
	}
}
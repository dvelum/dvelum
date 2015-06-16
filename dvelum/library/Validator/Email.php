<?php
/**
 * Email validator
 * @package Validator
 * @author Kirill A Egorov 2011
 */
class Validator_Email implements Validator_Interface
{
	/**
	 * Validate value
	 * @param string $value
	 * @return boolean
	 */
	static public function validate($value)
	{
		return filter_var($value , FILTER_VALIDATE_EMAIL);
	}
}
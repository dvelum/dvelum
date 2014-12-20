<?php
/**
 * Interface for validation classes
 * @package Validator
 * @author Kirill A Egorov 2011
 */
interface Validator_Interface{ 
    /**
     * Validation method
     * @param mixed $value
     * @return boolean
     */
    static public function validate($value);
}
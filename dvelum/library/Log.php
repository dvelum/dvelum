<?php
interface Log{
	public function log($level, $message, array $context = array());
}
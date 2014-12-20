<?php
class UserTest extends PHPUnit_Framework_TestCase
{
	public function testGetInstance()
	{
		$this->assertInstanceOf('User' , User::getInstance());
	}
}
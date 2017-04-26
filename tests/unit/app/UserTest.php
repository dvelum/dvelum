<?php
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
	public function testGetInstance()
	{
		$this->assertInstanceOf('User' , User::getInstance());
	}
}
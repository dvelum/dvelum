<?php
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
	public function testGetInstance()
	{
		$this->assertInstanceOf('\Dvelum\App\Session\User' , \Dvelum\App\Session\User::factory());
	}
}
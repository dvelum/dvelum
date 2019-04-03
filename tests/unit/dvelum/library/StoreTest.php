<?php

use Dvelum\Store\AdapterInterface;
use PHPUnit\Framework\TestCase;

use \Dvelum\Store\Factory;

class StoreTest extends TestCase
{
	public function testFactory()
	{
		$this->assertInstanceOf('\Dvelum\Store\AdapterInterface' , Factory::get());
		$this->assertInstanceOf('\Dvelum\Store\Local' , Factory::get(Factory::LOCAL));
		$this->assertInstanceOf('\Dvelum\Store\Session' , Factory::get(Factory::SESSION));
		$this->assertEquals(false , Store::factory('Undefined type'));
	}
}
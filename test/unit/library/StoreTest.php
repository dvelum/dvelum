<?php
class StoreTest extends PHPUnit_Framework_TestCase
{
	public function testFactory()
	{
		$this->assertInstanceOf('Store_Local' , Store::factory());
		$this->assertInstanceOf('Store_Local' , Store::factory(Store::Local));
		$this->assertInstanceOf('Store_Session' , Store::factory(Store::Session));
		$this->assertEquals(false , Store::factory('Undefined type'));
	}
}
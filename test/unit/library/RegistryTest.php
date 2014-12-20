<?php
class RegistryTest extends PHPUnit_Framework_TestCase
{
	public function testSet()
	{
		$a = array();
		$b = new stdClass();
		$c = 1;
		$d = 23.809;
		$e = 'some string';
		
		Registry::set('a', $a);
		Registry::set('b', $b);
		Registry::set('c', $c ,'c');
		Registry::set('d', $d ,'d');
		Registry::set('e', $e ,'c');
		
		
		$this->assertEquals($a,Registry::get('a'));
		$this->assertEquals($b,Registry::get('b'));
		$this->assertEquals($c,Registry::get('c','c'));
		$this->assertEquals($d,Registry::get('d','d'));
		$this->assertEquals($e,Registry::get('e','c'));
	}

	public function testRemove()
	{
		Registry::set('a','a');
		Registry::set('b', 'b');
		Registry::remove('b');
		
		$this->assertEquals(true,Registry::isValidKey('a'));
		$this->assertEquals(false,Registry::isValidKey('b'));	
	}
}
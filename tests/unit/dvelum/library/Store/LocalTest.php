<?php
use PHPUnit\Framework\TestCase;
use \Dvelum\Store\Factory;

class Store_LocalTest extends TestCase
{
	public function testGetCount()
	{
		$store = Factory::get(Factory::LOCAL , 'test');
		$this->assertEquals(0 , $store->getCount());
		$store->set('key','val');
		$this->assertEquals(1 , $store->getCount());
	}
	
	public function testGetData()
	{
		$store = Factory::get(Factory::LOCAL  , 'test');
		$store->set('key','val');
		$store->set('key2','val2');		
		$this->assertEquals( array('key'=>'val','key2'=>'val2'), $store->getData());
	}
	
	public function testSet()
	{
		$store = Factory::get(Factory::LOCAL  , 'test');
		$value = array('key'=>'val','key2'=>'val2');
		$store->set('key',$value);
		$this->assertEquals( $value, $store->get('key'));
		$this->assertEquals( null, $store->get('keywefw'));
	}
		
	public function testSetValues()
	{
		$store = Factory::get(Factory::LOCAL  , 'test');
		$values = ['key'=>'val','key2'=>'val2'];
		$store->setValues($values);	
		$v = $store->get('key2');
		$this->assertEquals( 'val2', $v);		
	}
	
	public function testRemove()
	{
		$store = Factory::get(Factory::LOCAL  , 'test');
		$store->set('key','val');
		$store->set('key2','val2');
		$store->remove('key');
		$this->assertFalse($store->keyExists('key'));
	}
	
	public function testClear()
	{
		$store = Factory::get(Factory::LOCAL  , 'test');
		$store->set('key','val');
		$store->set('key2','val2');
		$store->clear();
		$this->assertFalse($store->keyExists('key'));
		$this->assertFalse($store->keyExists('key2'));
	}
	
	public function testSetData()
	{
		$store = Factory::get(Factory::LOCAL  , 'test');
		$store->set('key','val');
		$data = array('key2'=>'val2','key3'=>'val3');
		$store->setData($data);
		$this->assertEquals( $data, $store->getData());
	}
}
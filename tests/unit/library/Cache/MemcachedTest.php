<?php
class Cache_MemcachedTest extends PHPUnit_Framework_TestCase
{
	protected function _connect(){
		$cfg = 	array(

								'compression' => 1 , 
								'normalizeKeys' => 0 , 
								'defaultLifeTime' => 604800 ,  // 7 days
								'keyPrefix' => 'dv_sys' , 
								'servers' => array(
										array(
												'host' => 'localhost' , 
												'port' => 11211 , 
												'persistent' => true , 
												'weight' => 1 , 
												'timeout' => 5 , 
												'retry_interval' => 15 , 
												'status' => true
										)
								)	
		);		
		return new Cache_Memcache($cfg);
		
	}
	
	public function testSave()
	{
		$cache = $this->_connect();
		$data2 = array('id'=>1,'name'=>'somename');
		$data3 = new stdClass();
		$data3->id = 1;
		$this->assertTrue($cache->save('data', 'somekey'));
		$this->assertTrue($cache->save($data2, 'somekey2'));
		$this->assertTrue($cache->save($data3, 'somekey3'));
		$this->assertEquals($cache->load('somekey'),'data');
		$this->assertEquals($cache->load('somekey2'),$data2);
		$this->assertEquals($cache->load('somekey3'),$data3);
	}

	public function testClean()
	{
		$cache = $this->_connect();
		$cache->save('data', 'somekey');
		$cache->save('data2', 'somekey2');
		$cache->clean();
		$this->assertFalse($cache->load('somekey'));
		$this->assertFalse($cache->load('somekey2'));
	}

	public function testRemove()
	{
		$cache = $this->_connect();
		$cache->save('data', 'somekey');
		$cache->save('data2', 'somekey2');
		$cache->remove('somekey2');
		$this->assertEquals('data',$cache->load('somekey'));
		$this->assertFalse($cache->load('somekey2'));
	}
}
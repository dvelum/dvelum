<?php
class UtilsTest extends PHPUnit_Framework_TestCase
{
	
	public function testRekey()
	{
		$data = array(
			array('id'=>11,'text'=>1),
			array('id'=>12,'text'=>2),
			array('id'=>13,'text'=>3)
		);
		
		$result = Utils::rekey('id', $data);
		
		$this->assertEquals(array(
			11 => array('id'=>11,'text'=>1),
			12 => array('id'=>12,'text'=>2),
			13 => array('id'=>13,'text'=>3)
		), $result);
	}
	
	
	public function testCollectData(){
		$data = array(
			array('id'=>11,'text'=>1),
			array('id'=>12,'text'=>2),
			array('id'=>13,'text'=>3)
		);
		
		$result = Utils::collectData('id','text' , $data);
		
		$this->assertEquals(array(
			11 => 1,
			12 => 2,
			13 => 3
		), $result);
	}
	
	public function testFetchCol(){
		$data = array(
			array('id'=>11,'text'=>1),
			array('id'=>12,'text'=>2),
			array('id'=>13,'text'=>3)
		);
		
		$result = Utils::fetchCol('text', $data);
		$this->assertEquals(array(
			1,
			2,
			3
		), $result);
		
	}
	
	public function testGroupByKey()
	{
		
		$data = array(
			array('id'=>11,'text'=>1 ,'group'=>1),
			array('id'=>12,'text'=>2 ,'group'=>7),
			array('id'=>13,'text'=>3 ,'group'=>7)
		);
		
		$result = Utils::groupByKey('group', $data);
		
		$this->assertEquals(array(
			1 => array(
					array('id'=>11,'text'=>1 ,'group'=>1),
			),
			7 => array(
				array('id'=>12,'text'=>2 ,'group'=>7),
				array('id'=>13,'text'=>3 ,'group'=>7)
			)
		), $result);
	}
	
	public function testHash(){
		$hash1 = Utils::hash('abc');
		$hash2 = Utils::hash('abc');
		$hash3 = Utils::hash('abcd');
		
		$this->assertEquals(32 , strlen($hash1));
		$this->assertEquals($hash1 , $hash2);
		$this->assertTrue($hash1 !== $hash3);		
	}
	
	public function testFormatFileSize(){
	
	}
	
	public function testFormatTime(){
		
	}
	
	public function testExportArray(){
	
	}
	
	public function testExportCode(){
	
	}
	
	public function testClassFromPath()
	{	
		$this->assertEquals('Store_Session1' , Utils::classFromPath('store/session1.php'));
		$this->assertEquals('Store_Session2' , Utils::classFromPath('/Store/Session2.php'));
		$this->assertEquals('Store_Session3' , Utils::classFromPath('./Store/Session3.php'));
		$this->assertEquals('Store_Session4' , Utils::classFromPath('../Store/Session4.php'));	
		$this->assertEquals(false , Utils::classFromPath('Store/Session/'));
	}
}

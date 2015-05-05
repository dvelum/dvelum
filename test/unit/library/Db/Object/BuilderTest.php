<?php

class Db_Object_BuilderTest extends PHPUnit_Framework_TestCase
{
	public function testCreateObject()
	{	
		$o = new Db_Object_Builder('Page');
		$this->assertTrue($o instanceof Db_Object_Builder);
	}
	
	public function testTableExists()
	{
		$o = new Db_Object_Builder('Page');		
		$this->assertTrue($o->tableExists());
	}
	

	public function testValidate()
	{		
		$o = new Db_Object_Builder('Page');
		$o->build();
		$this->assertTrue($o->validate());	
	}
	
	public function testRenameTable()
	{
		$cfg = Db_Object_Config::getInstance('Page' , true);

		$uniqName = uniqid();
		$o = new Db_Object_Builder('Page',false);

		$renamed = $o->renameTable($uniqName);

		if(!$renamed)
		    echo implode("\n", $o->getErrors());
	
		$this->assertTrue($renamed);
		$cfg->getConfig()->set('table',$uniqName);

		Model::removeInstance('Page');
		$o = new Db_Object_Builder('Page',false);

		$renamed = $o->renameTable('content');

		if(!$renamed)
		  echo implode("\n", $o->getErrors());
		
 		$this->assertTrue($renamed);
		$cfg->getConfig()->set('table','content');

	}
	
	
	public function testCheckEngineCompatibility()
	{
		$o = new Db_Object_Builder('Page');
		$this->assertTrue($o->checkEngineCompatibility('myisam'));
		$this->assertTrue($o->checkEngineCompatibility('innodb'));
		$this->assertTrue(is_array($o->checkEngineCompatibility('memory')));
		
		$invalidEngine = false;	
		try{
			$o->checkEngineCompatibility('ksdhuis');
			
		} catch (Exception $e){
			$invalidEngine = true;
		}
		$this->assertTrue($invalidEngine);
		
	}
	
	
// 	public function prepareColumnUpdates()
	
// 	public function build()
	
// 	public function checkEngineCompatibility($newEngineType)
	
	
// 	public function changeTableEngine($engine)
	
// 	/**
// 	 * Remove object
// 	 * @param string $name
// 	 * @return boolean
// 	 */
// 	public function remove()
}
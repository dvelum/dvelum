<?php
class ModelTest extends PHPUnit_Framework_TestCase
{
	public function testFactory()
	{
		$pageModel =  Model::factory('Page');
		$this->assertEquals(( $pageModel instanceof Model_Page ) , true);
		$this->assertEquals($pageModel->getObjectName('page') , 'page');
		
		$apiKeys = Model::factory('apikeys');
		$this->assertEquals(( $apiKeys instanceof Model) , true);
		$this->assertEquals($apiKeys->getObjectName('apikeys') , 'apikeys');
	}
	
	public function testTable()
	{
		$dbCfg = Registry::get('db','config');
		$apiKeys = Model::factory('apikeys');
		$this->assertEquals($dbCfg['prefix'] . 'apikeys' , $apiKeys->table());
	}
	
	public function testGetItem()
	{
		$pageModel =  Model::factory('Page');
		$item = $pageModel->getItem(1,array('id','code'));
		$this->assertEquals('index' , $item['code']);	
	}
	
	public function testListIntegers()
	{
		$this->assertEquals('1,2,3,4,5', Model::listIntegers(array('1','2','3','4','5')));
	}
	
	
	public function testGetCount()
	{
		$pageModel =  Model::factory('Page');
		$this->assertEquals(1 , $pageModel->getCount(array('code'=>'index')));
	}
	/**
	 * @todo check params , filters
	 */
	public function testGetList()
	{
		$pageModel =  Model::factory('Page');
		$items = $pageModel->getList(array(),array('code'=>'index'),array('id','code'));
		$this->assertEquals('index' , $items[0]['code'] = 'index');	
	}
	/**
	 * @todo add assertations
	 */
	public function testGetListVc()
	{
		$pageModel =  Model::factory('Page');
		$items = $pageModel->getListVc(array(),array('code'=>'index'), false , array('id','code'));
		$this->assertEquals('index' , $items[0]['code']);	
		
		$userModel =  Model::factory('User');
		$items = $userModel->getListVc(false, false, 'Admin', array('id','login'));
		$this->assertEquals('root' , $items[0]['login']);			
	}
}
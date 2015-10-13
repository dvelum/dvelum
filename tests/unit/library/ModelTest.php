<?php
class ModelTest extends PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		parent::__construct();
		$this->clear();
	}
	protected function createPage()
	{
		$group = new Db_Object('Group');
		$group->setValues(array(
			'title' => date('YmdHis'),
			'system' =>false
		));
		$group->save();

		$user = new Db_Object('User');
		try{
			$user->setValues(array(
				'login'=>uniqid().date('YmdHis'),
				'pass'=>'111',
				'email'=>uniqid().date('YmdHis').'@mail.com',
				'enabled'=>1,
				'admin'=>1,
				'name'=>'Test User',
				'group_id'=>$group->getId()
			));
		}catch(Exception $e){
			echo $e->getMessage();
		}
		$saved = $user->save();
		$this->assertTrue(!empty($saved));

		$u = User::getInstance();
		$u->setId($user->getId());
		$u->setAuthorized();

		$page = new Db_Object('Page');
		$page->setValues(array(
			'code'=>uniqid().date('YmdHis'),
			'is_fixed'=>1,
			'html_title'=>'Index',
			'menu_title'=>'Index',
			'page_title'=>'Index',
			'meta_keywords'=>'',
			'meta_description'=>'',
			'parent_id'=>null,
			'text' =>'[Index page content]',
			'func_code'=>'',
			'order_no' => 1,
			'show_blocks'=>true,
			'published'=>true,
			'published_version'=>0,
			'editor_id'=>$user->getId(),
			'author_id'=>$user->getId(),
			'date_created'=>date('Y-m-d H:i:s'),
			'date_updated'=>date('Y-m-d H:i:s'),
			'author_id'=>$user->getId(),
			'blocks'=>'',
			'theme'=>'default',
			'date_published'=>date('Y-m-d H:i:s'),
			'in_site_map'=>true,
			'default_blocks'=>true
		));
		$page->save();
		return $page;
	}

	protected function clear()
	{
		Model::factory('Page')->getDbConnection()->delete(Model::factory('Page')->table());
		Model::factory('Group')->getDbConnection()->delete(Model::factory('Group')->table());
	}

	public function testFactory()
	{
		$pageModel =  Model::factory('Page');
		$this->assertEquals(( $pageModel instanceof Model_Page ) , true);
		$this->assertEquals($pageModel->getObjectName() , 'page');
		
		$apiKeys = Model::factory('User');
		$this->assertEquals(( $apiKeys instanceof Model) , true);
		$this->assertEquals($apiKeys->getObjectName() , 'user');
	}
	
	public function testTable()
	{
		$dbCfg = Registry::get('db','config');
		$apiKeys = Model::factory('User');
		$this->assertEquals($dbCfg['prefix'] . 'user' , $apiKeys->table());
	}
	
	public function testGetItem()
	{
		$pageModel =  Model::factory('Page');
		$page = $this->createPage();
		$item = $pageModel->getItem($page->getId(),array('id','code'));
		$this->assertEquals($page->get('code') , $item['code']);
	}
	
	public function testListIntegers()
	{
		$this->assertEquals('1,2,3,4,5', Model::listIntegers(array('1','2','3','4','5')));
	}
	
	
	public function testGetCount()
	{
		$page = $this->createPage();
		$pageModel =  Model::factory('Page');
		$this->assertEquals(1 , $pageModel->getCount(array('code'=>$page->get('code'))));
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
		$page = $this->createPage();
		$items = $pageModel->getListVc(array(),array('code'=>$page->get('code')), false , array('id','code'));
		$this->assertEquals($page->get('code') , $items[0]['code']);
	}
}
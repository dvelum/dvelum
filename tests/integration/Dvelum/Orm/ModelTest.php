<?php
use PHPUnit\Framework\TestCase;
use Dvelum\Orm\Model;
use Dvelum\Orm\Record;

class ModelTest extends TestCase
{
	public function __construct()
	{
		parent::__construct();
		$this->clear();
	}

	protected function createPage()
	{
		$group = Record::factory('Group');
		$group->setValues(array(
			'title' => date('YmdHis'),
			'system' =>false
		));
		$group->save();

		$user = Record::factory('User');
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

		$page = Record::factory('Page');
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
        $userModel = Model::factory('User');
        $dbCfg = $userModel->getDbConnection()->getConfig();
        $userModel = Model::factory('User');
		$this->assertEquals($dbCfg['prefix'] . 'user' , $userModel->table());
	}
	
	public function testGetItem()
	{
		$pageModel =  Model::factory('Page');
		$page = $this->createPage();
		$page2 = $this->createPage();
		$item = $pageModel->getItem($page->getId(),array('id','code'));
		$this->assertEquals($page->get('code') , $item['code']);

		$item2 = $pageModel->getCachedItem($page->getId());
		$this->assertEquals($item['code'], $item2['code']);

		$this->assertFalse($pageModel->checkUnique($page->getId(), 'code', $page2->get('code')));
        $this->assertTrue($pageModel->checkUnique($page->getId(), 'code', $page2->get('code').'1'));
	}

	public function testGetCount()
	{
		$page = $this->createPage();
		$pageModel =  Model::factory('Page');
		$this->assertEquals(1 , $pageModel->query()->filters(array('code'=>$page->get('code')))->getCount());
	}
	/**
	 * @todo check params , filters
	 */
	public function testGetList()
	{
		$pageModel =  Model::factory('Page');
		$items = $pageModel->query()->filters(array('code'=>'index'))->fields(array('id','code'))->fetchAll();
		$this->assertEquals('index' , $items[0]['code'] = 'index');	
	}
	/**
	 * @todo add assertations
	 */
	public function testGetListVc()
	{
		$pageModel =  Model::factory('Page');
		$page = $this->createPage();
		$items = $pageModel->query()->filters(array('code'=>$page->get('code')))->fields(array('id','code'))->fetchAll();
		$this->assertEquals($page->get('code') , $items[0]['code']);
	}

	public function testGetObjectConfig()
    {
        $model = Model::factory('Page');
        $config = $model->getObjectConfig();
        $this->assertTrue($config instanceof Record\Config);
        $this->assertEquals('page', $config->getName());
    }

    public function testRemove()
    {
        $model = Model::factory('Page');
        $page = $this->createPage();
        $this->assertTrue($model->remove($page->getId()));
        $this->assertFalse($model->remove($page->getId()));
        $this->assertEquals(0 , $model->query()->filters(['id'=>$page->getId()])->getCount());
    }

    public function testInsert()
    {
        $model = Model::factory('Page');
        $insert = $model->insert();
        $this->assertTrue($insert instanceof Model\Insert);
    }
}
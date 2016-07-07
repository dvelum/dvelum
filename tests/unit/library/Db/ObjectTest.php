<?php
class Db_ObjectTest extends PHPUnit_Framework_TestCase
{
	protected function createPage()
	{
		$group = new Db_Object('Group');
		$group->setValues(array(
			'title' => date('YmdHis'),
			'system' =>false
		));
		$group->save();

		$user = new Db_Object('User');
		try {
			$user->setValues(array(
				'login' => uniqid(date('YmdHis')),
				'pass' => '111',
				'email' => uniqid(date('YmdHis')) . '@mail.com',
				'enabled' => 1,
				'admin' => 1,
				'name'=>'Test User',
				'group_id' => $group->getId()
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
	public function testSave()
	{
		$page = $this->createPage();
		$o = new Db_Object('page' , $page->getId());
		$this->assertEquals($o->code, $page->get('code'));
		$code = date('ymdHis').'testSave';
		$this->assertTrue($o->set('code', $code));
		$saved = $o->save();
		$this->assertTrue(!empty($saved));
	}

	public function testGetOld()
	{
		$o = $this->createPage();
		$oldCode = $o->get('code');
		$code = date('ymdHis');
		$o->set('code', $code);
		$this->assertEquals($o->get('code') , $code);
		$this->assertEquals($o->getOld('code'), $oldCode);
	}

	public function testCreate()
	{
		$o = new Db_Object('bgtask');
		$this->assertTrue($o->set('status', 1));
		$this->assertTrue($o->set('time_started', date('Y-m-d H:i:s')));
		$this->assertTrue($o->set('memory', 1024));
		$this->assertTrue($o->set('op_finished', 0));
		$this->assertTrue($o->set('op_total', 10));
		$this->assertTrue($o->set('title', 'Title'));

		$this->assertTrue((boolean)$o->save());
	}

	public function testFactory(){
		$page = $this->createPage();
		$o = Db_Object::factory('Page' , $page->getId());
		$o2 = new Db_Object('page' , $page->getId());
		$this->assertEquals($o, $o2);
	}

	public function testHasUpdates()
	{
		$o = $this->createPage();
		$this->assertFalse($o->hasUpdates());
		$o->set('page_title', 'new title');
		$this->assertTrue($o->hasUpdates());
	}

	public function testToString()
	{
		$o = $this->createPage();
		$this->assertEquals($o->__toString(), (string) $o->getId());
	}

	public function testObjectExists()
	{
		$page = $this->createPage();

		$this->assertFalse(Db_Object::objectExists('ckwjhebjfcwe', false));
		$this->assertFalse(Db_Object::objectExists('Page', 999999));
		$this->assertTrue(Db_Object::objectExists('Page', array($page->getId())));
		$this->assertTrue(Db_Object::objectExists('Page', $page->getId()));
	}

	public function testDeleteObject()
	{
		$page = $this->createPage();
		$id = $page->getId();
		$this->assertTrue($page->getId()>0);
		$this->assertTrue(Db_Object::objectExists('Page', $id));
		$this->assertTrue($page->delete());
		$this->assertFalse(Db_Object::objectExists('Page', $id));
	}

	public function testGetLinkedObject()
	{
		$o = $this->createPage();
		$linked = $o->getLinkedObject('author_id');
		$this->assertEquals($linked , 'user');
	}


	public function test_hasRequired()
	{
		$somePage = $this->createPage();
		$page = new Db_Object('page');
		$code = date('ymdHiss');
		$page->code = $code;
		$page->author_id = 1;

		$this->assertFalse($page->save());

		$page = $this->createPage();

		$this->assertTrue($page->getId()>0);
		$this->assertTrue(Db_Object::objectExists('Page', $page->getId()));
		$this->assertTrue($page->delete());
		$this->assertFalse(Db_Object::objectExists('Page', $page->getId()));
	}

	public function testExists()
	{
	   $this->assertFalse(Db_Object::objectExists('page' , 723489273));
	   $this->assertFalse(Db_Object::objectExists('undefined' , 723489273));
	}

	public function testSet()
	{
		$object_a = $this->createPage();
		$object_b = $this->createPage();
		$this->assertTrue($object_a->set('parent_id',$object_b));
		$this->assertEquals($object_a->get('parent_id') , $object_b->getId());
		try{
			$object_a->set('parent_id', new Db_Object('User', User::getInstance()->id));
			$this->fail('No exception thrown');
		}catch (Exception $e){}
	}

	public function testIsInstanceOf()
	{
		$o = new Db_Object('Page');
		$this->assertTrue($o->isInstanceOf('Page'));
		$this->assertFalse($o->isInstanceOf('User'));
	}

    public function testSetInsertId()
	{
		$somePage = $this->createPage();

		$iId = time();
		$o = new Db_Object('Page');
		$o->setInsertId($iId);
		$userId = User::getInstance()->id;

		$this->assertEquals($iId , $o->getInsertId());
		$o->setValues(array(
	      'code'=>$iId,
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
	      'editor_id'=>$userId,
	      'date_created'=>date('Y-m-d H:i:s'),
	      'date_updated'=>date('Y-m-d H:i:s'),
	      'author_id'=>$userId,
	      'blocks'=>'',
	      'theme'=>'default',
	      'date_published'=>date('Y-m-d H:i:s'),
	      'in_site_map'=>true,
	      'default_blocks'=>true
	  ));
	  $this->assertTrue((boolean) $o->save());

	  $this->assertTrue(Db_Object::objectExists('Page', $iId));
	  $this->assertEquals($iId, $o->getId());
	}

	public function testGetTitle()
	{
		$page = new Db_Object('Page');
		$cfg = $page->getConfig();

		$data = $cfg->getData();
		$data['link_title'] = '/ {code} / {menu_title} /';

		$cfg->setData($data);

		$page->set('code' , 'pageCode');
		$page->set('menu_title' , 'pageTitle');

		//echo $page->getTitle();exit;
		$this->assertEquals('/ pageCode / pageTitle /' , $page->getTitle());
	}
}
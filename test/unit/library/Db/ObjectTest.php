<?php
class Db_ObjectTest extends PHPUnit_Framework_TestCase
{
	public function testSave()
	{
		$o = new Db_Object('page' , 1);
		$this->assertEquals($o->code, 'index');
		$code = date('ymdHis');
		$o->set('code', $code);
		$o->save();
		$o->set('code', 'index');
		$o->save();
	}

	public function testGetOld()
	{
		$o = new Db_Object('page' , 1);
		$code = date('ymdHis');
		$o->set('code', $code);
		$this->assertEquals($o->get('code') , $code);
		$this->assertEquals($o->getOld('code'), 'index');
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
		$o = Db_Object::factory('Page' , 1);
		$o2 = new Db_Object('page' , 1);
		$this->assertEquals($o, $o2);
	}

	public function testHasUpdates()
	{
		$o = new Db_Object('page' , 1);
		$this->assertFalse($o->hasUpdates());
		$o->set('page_title', 'new title');
		$this->assertTrue($o->hasUpdates());
	}

	public function testToString()
	{
		$o = new Db_Object('page' , 1);
		$this->assertEquals($o->__toString(), '1');
	}

	public function testObjectExists()
	{
		$this->assertFalse(Db_Object::objectExists('ckwjhebjfcwe', false));
		$this->assertFalse(Db_Object::objectExists('Page', 999999));
		$this->assertTrue(Db_Object::objectExists('Page', array(1)));
		$this->assertTrue(Db_Object::objectExists('Page', 1));
	}

	public function testDeleteObject()
	{
	  $u = User::getInstance();
	  $u->setId(1);
	  $u->setAuthorized();

	  $code = date('ymdHis');
	  // Add index Page
	  $page = new Db_Object('Page');
	  $page->setValues(array(
	      'code'=>$code,
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
	      'editor_id'=>1,
	      'date_created'=>date('Y-m-d H:i:s'),
	      'date_updated'=>date('Y-m-d H:i:s'),
	      'author_id'=>1,
	      'blocks'=>'',
	      'theme'=>'default',
	      'date_published'=>date('Y-m-d H:i:s'),
	      'in_site_map'=>true,
	      'default_blocks'=>true
	  ));

		$id = $page->save();

		$this->assertTrue($id>0);
		$this->assertTrue(Db_Object::objectExists('Page', $id));

		$this->assertTrue($page->delete());

		$this->assertFalse(Db_Object::objectExists('Page', $id));
	}

	public function testGetLinkedObject()
	{
		$o = new Db_Object('page' , 1);
		$linked = $o->getLinkedObject('author_id');
		$this->assertEquals($linked , 'user');
	}


	public function test_hasRequired()
	{
	  $u = User::getInstance();
	  $u->setId(1);
	  $u->setAuthorized();

		$page = new Db_Object('page');
		$code = date('ymdHiss');
		$page->code = $code;
		$page->author_id = 1;

		$this->assertFalse($page->save());

		$page->setValues(array(
	      'code'=>$code,
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
	      'editor_id'=>1,
	      'date_created'=>date('Y-m-d H:i:s'),
	      'date_updated'=>date('Y-m-d H:i:s'),
	      'author_id'=>1,
	      'blocks'=>'',
	      'theme'=>'default',
	      'date_published'=>date('Y-m-d H:i:s'),
	      'in_site_map'=>true,
	      'default_blocks'=>true
	  ));

		$id = $page->save();
		$this->assertTrue($id>0);
		$this->assertTrue(Db_Object::objectExists('Page', $id));
		$this->assertTrue($page->delete());
		$this->assertFalse(Db_Object::objectExists('Page', $id));
	}

	public function testExists()
	{
	   $this->assertTrue(Db_Object::objectExists('page' , 1));
	   $this->assertFalse(Db_Object::objectExists('page' , 723489273));
	   $this->assertFalse(Db_Object::objectExists('undefined' , 723489273));
	}

    public function testSetInsertId()
	{
		$iId = time();
		$o = new Db_Object('Page');
		$o->setInsertId($iId);

		$this->assertEquals($iId , $o->getInssertId());
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
	      'editor_id'=>1,
	      'date_created'=>date('Y-m-d H:i:s'),
	      'date_updated'=>date('Y-m-d H:i:s'),
	      'author_id'=>1,
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
		$cfg = Db_Object_Config::getInstance('Page');
		$data = $cfg->getData();
		$data['link_title'] = '/ {code} / {menu_title} /';
		$cfg->setData($data);

		$page = new Db_Object('Page');
		$page->set('code' , 'pageCode');
		$page->set('menu_title' , 'pageTitle');

		//echo $page->getTitle();exit;
		$this->assertEquals('/ pageCode / pageTitle /' , $page->getTitle());
	}
}
<?php 
class Db_Object_ConfigTest extends PHPUnit_Framework_TestCase
{
	
	public function testRenameField()
	{
		$o = new Db_Object_Builder('Page');
		$cfg = Db_Object_Config::getInstance('Page');
		
		$this->assertTrue($cfg->renameField('page_title', 'untitle'));
		$this->assertTrue($o->validate());
		$this->assertTrue($cfg->renameField('untitle', 'page_title'));
		$this->assertTrue($o->validate());
	}
	
	public function testGetTable()
	{	
		$cfg = Db_Object_Config::getInstance('Page');
		$prefix = Model::factory('page')->getDbPrefix();		
		$this->assertEquals($cfg->getTable(false), $cfg->get('table'));
		$this->assertEquals($cfg->getTable(), $prefix . $cfg->get('table'));
	}
	

	public function testGetObjectTtile()
	{
		$cfg =  Db_Object_Config::getInstance('Page');
		$oldTitle = $cfg->getTitle();
		$cfg->setObjectTitle('My title');	
		$this->assertEquals($cfg->getTitle() , 'My title');
		$cfg->setObjectTitle($oldTitle);
	}
	
	public function testSave()
	{
		$cfg =  Db_Object_Config::getInstance('Page');
		$oldTitle = $cfg->getTitle();
		$cfg->setObjectTitle('My title');
		$this->assertTrue($cfg->save());
		$cfg->setObjectTitle($oldTitle);
		$this->assertTrue($cfg->save());
	}
	
	public function testRemoveField()
	{
		$cfg =  Db_Object_Config::getInstance('Page');
		$fldCfg = $cfg->getFieldConfig('page_title');
		$cfg->removeField('page_title');
		$this->assertFalse($cfg->fieldExists('page_title'));
		$cfg->setFieldConfig('page_title', $fldCfg);
		$this->assertTrue($cfg->fieldExists('page_title'));
	}
	
	public function testIsText()
	{
		$cfg =  Db_Object_Config::getInstance('Page');
		$this->assertTrue($cfg->isText('text'));
		$this->assertFalse($cfg->isText('id'));
	}
	
	public function testIndexExists()
	{
		$cfg =  Db_Object_Config::getInstance('Page');
		$this->assertTrue($cfg->indexExists('PRIMARY'));
		$this->assertFalse($cfg->indexExists('undefinedindex'));
	}
	
	public function testIsUnique()
	{
		$cfg =  Db_Object_Config::getInstance('Page');
		$this->assertTrue($cfg->isUnique('id'));
		$this->assertTrue($cfg->isUnique('code'));
		$this->assertFalse($cfg->isUnique('title'));
		$this->assertFalse($cfg->isUnique('parent_id'));
	}
	
	public function testIsHtml()
	{
	  $cfg = Db_Object_Config::getInstance('page');
	  $this->assertTrue($cfg->isHtml('text'));
	  $this->assertFalse($cfg->isHtml('code'));  
	}
	
	public function testIsNumeric()
	{
	  $cfg = Db_Object_Config::getInstance('page');
	  $this->assertTrue($cfg->isNumeric('id'));
	  $this->assertFalse($cfg->isNumeric('code'));
	}
	
	public function testIsInteger()
	{
	  $cfg = Db_Object_Config::getInstance('page');
	  $this->assertTrue($cfg->isInteger('id'));
	  $this->assertFalse($cfg->isInteger('code'));
	}
	
	public function testIsSearch()
	{
	  $cfg = Db_Object_Config::getInstance('page');
	  $this->assertTrue($cfg->isSearch('code'));
	  $this->assertTrue($cfg->isSearch('id'));
	}
	
	public function testGetLinkTittle()
	{
	  $cfg = Db_Object_Config::getInstance('page');
	  $this->assertEquals($cfg->getLinkTitle() , 'menu_title');
	}
	
	public function testIsFloat()
	{
	  $cfg = Db_Object_Config::getInstance('test');
	  $this->assertFalse($cfg->isFloat('integer'));  
	  $this->assertTrue($cfg->isFloat('float'));
	}
	
	public function testIsSystem()
	{
	  $cfg = Db_Object_Config::getInstance('test');
	  $this->assertFalse($cfg->isSystem());
	  $cfg = Db_Object_Config::getInstance('page');
	  $this->assertTrue($cfg->isSystem());
	}
	
	public function testgetLinkTitle()
	{
	  $cfg = Db_Object_Config::getInstance('test');
	  $this->assertEquals($cfg->getLinkTitle() , $cfg->getPrimaryKey());
	}
	
	public function testgetDbType()
	{
	  $cfg = Db_Object_Config::getInstance('test');
	  $this->assertEquals('bigint', $cfg->getDbType($cfg->getPrimaryKey()));
	  $this->assertEquals('float', $cfg->getDbType('float'));
	}
	
	public function testHasHistory()
	{
	  $cfg = Db_Object_Config::getInstance('test');
	  $this->assertTrue($cfg->hasHistory());
	  $cfg = Db_Object_Config::getInstance('historylog');
	  $this->assertFalse($cfg->hasHistory());
	}
	
	public function testIsObjectLink()
	{
	  $cfg = Db_Object_Config::getInstance('test');
	  $this->assertTrue($cfg->isObjectLink('link'));
	  $this->assertFalse($cfg->isObjectLink('multilink'));
	  $this->assertFalse($cfg->isObjectLink('integer'));
	  $this->assertFalse($cfg->isObjectLink('dictionary'));
	}
	
	public function testIsMultiLink()
	{
	  $cfg = Db_Object_Config::getInstance('test');
	  $this->assertTrue($cfg->isMultiLink('multilink'));
	  $this->assertFalse($cfg->isMultiLink('link'));
	  $this->assertFalse($cfg->isMultiLink('dictionary'));
	  $this->assertFalse($cfg->isMultiLink('integer'));
	}
	
	public function testGetLinkedObject()
	{
	  $cfg = Db_Object_Config::getInstance('test');
	  $this->assertEquals('user', $cfg->getLinkedObject('link'));
	  $this->assertEquals('page', $cfg->getLinkedObject('multilink'));
	}
	
	public function testGetLinkedDictionary()
	{
	  $cfg = Db_Object_Config::getInstance('test');
	  $this->assertEquals('link_type', $cfg->getLinkedDictionary('dictionary'));
	}
}
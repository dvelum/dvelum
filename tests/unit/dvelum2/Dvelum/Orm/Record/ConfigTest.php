<?php
use PHPUnit\Framework\TestCase;
use Dvelum\Orm\Record\Builder;
use Dvelum\Orm\Record;
use Dvelum\Orm\Model;

class ConfigTest extends TestCase
{
	
	public function testRenameField()
	{
		$o = Builder::factory('Page');
		$cfg = Record\Config::factory('Page');
		
		$this->assertTrue($cfg->renameField('page_title', 'untitle'));
		$this->assertTrue($o->validate());
		$this->assertTrue($cfg->renameField('untitle', 'page_title'));
		$this->assertTrue($o->validate());
	}
	
	public function testGetTable()
	{	
		$cfg = Record\Config::factory('Page');
		$prefix = Model::factory('page')->getDbPrefix();		
		$this->assertEquals($cfg->getTable(false), $cfg->get('table'));
		$this->assertEquals($cfg->getTable(), $prefix . $cfg->get('table'));
	}
	

	public function testGetObjectTtile()
	{
		$cfg = Record\Config::factory('Page');
		$oldTitle = $cfg->getTitle();
		$cfg->setObjectTitle('My title');	
		$this->assertEquals($cfg->getTitle() , 'My title');
		$cfg->setObjectTitle($oldTitle);
	}
	
	public function testSave()
	{
		$cfg =  Record\Config::factory('Page');
		$oldTitle = $cfg->getTitle();
		$cfg->setObjectTitle('My title');
		$this->assertTrue($cfg->save());
		$cfg->setObjectTitle($oldTitle);
		$this->assertTrue($cfg->save());
	}
	
	public function testRemoveField()
	{
		$cfg = Record\Config::factory('Page');
		$fldCfg = $cfg->getFieldConfig('page_title');
		$cfg->removeField('page_title');
		$this->assertFalse($cfg->fieldExists('page_title'));
		$cfg->setFieldConfig('page_title', $fldCfg);
		$this->assertTrue($cfg->fieldExists('page_title'));
	}
	
	public function testIsText()
	{
		$cfg = Record\Config::factory('Page');
		$this->assertTrue($cfg->getField('text')->isText());
		$this->assertFalse($cfg->getField('id')->isText());
	}
	
	public function testIndexExists()
	{
		$cfg =  Record\Config::factory('Page');
		$this->assertTrue($cfg->indexExists('PRIMARY'));
		$this->assertFalse($cfg->indexExists('undefinedindex'));
	}
	
	public function testIsUnique()
	{
		$cfg = Record\Config::factory('Page');
		$this->assertTrue($cfg->getField('id')->isUnique());
		$this->assertTrue($cfg->getField('code')->isUnique());
		$this->assertFalse($cfg->getField('title')->isUnique());
		$this->assertFalse($cfg->getField('parent_id')->isUnique());
	}
	
	public function testIsHtml()
	{
	  $cfg = Record\Config::factory('page');
	  $this->assertTrue($cfg->getField('text')->isHtml());
	  $this->assertFalse($cfg->getField('code')->isHtml());
	}
	
	public function testIsNumeric()
	{
	  $cfg = Record\Config::factory('page');
	  $this->assertTrue($cfg->getField('id')->isNumeric());
	  $this->assertFalse($cfg->getField('code')->isNumeric());
	}
	
	public function testIsInteger()
	{
	  $cfg = Record\Config::factory('page');
	  $this->assertTrue($cfg->getField('id')->isInteger());
	  $this->assertFalse($cfg->getField('code')->isInteger());
	}
	
	public function testIsSearch()
	{
	  $cfg = Record\Config::factory('page');
	  $this->assertTrue($cfg->getField('id')->isSearch('code'));
	  $this->assertTrue($cfg->getField('code')->isSearch('id'));
	}
	
	public function testGetLinkTittle()
	{
	  $cfg = Record\Config::factory('page');
	  $this->assertEquals($cfg->getLinkTitle() , 'menu_title');
	}
	
	public function testIsFloat()
	{
	  $cfg = Record\Config::factory('test');
	  $this->assertFalse($cfg->getField('integer')->isFloat());
	  $this->assertTrue($cfg->getField('float')->isFloat());
	}
	
	public function testIsSystem()
	{
	  $cfg = Record\Config::factory('test');
	  $this->assertFalse($cfg->isSystem());
	  $cfg = Record\Config::factory('page');
	  $this->assertTrue($cfg->isSystem());
	}
	
	public function testgetLinkTitle()
	{
	  $cfg = Record\Config::factory('test');
	  $this->assertEquals($cfg->getLinkTitle() , $cfg->getPrimaryKey());
	}
	
	public function testgetDbType()
	{
	  $cfg = Record\Config::factory('test');
	  $this->assertEquals('bigint', $cfg->getField($cfg->getPrimaryKey())->getDbType());
	  $this->assertEquals('float', $cfg->getField('float')->getDbType());
	}
	
	public function testHasHistory()
	{
	  $cfg = Record\Config::factory('test');
	  $this->assertTrue($cfg->hasHistory());
	  $cfg = Record\Config::factory('historylog');
	  $this->assertFalse($cfg->hasHistory());
	}
	
	public function testIsObjectLink()
	{
	  $cfg = Record\Config::factory('test');
	  $this->assertTrue($cfg->getField('link')->isObjectLink());
	  $this->assertFalse($cfg->getField('multilink')->isObjectLink());
	  $this->assertFalse($cfg->getField('integer')->isObjectLink());
	  $this->assertFalse($cfg->getField('dictionary')->isObjectLink('dictionary'));
	}
	
	public function testIsMultiLink()
	{
	  $cfg = Record\Config::factory('test');
	  $this->assertTrue($cfg->getField('multilink')->isMultiLink());
	  $this->assertFalse($cfg->getField('link')->isMultiLink());
	  $this->assertFalse($cfg->getField('dictionary')->isMultiLink());
	  $this->assertFalse($cfg-->getField('integer')->isMultiLink());
	}
	
	public function testGetLinkedObject()
	{
	  $cfg = Record\Config::factory('test');
	  $this->assertEquals('user', $cfg->getLinkedObject('link'));
	  $this->assertEquals('page', $cfg->getLinkedObject('multilink'));
	}
	
	public function testGetLinkedDictionary()
	{
	  $cfg = Record\Config::factory('test');
	  $this->assertEquals('link_type', $cfg->getLinkedDictionary('dictionary'));
	}
}
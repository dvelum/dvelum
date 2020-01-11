<?php

use PHPUnit\Framework\TestCase;
use Dvelum\Orm\Record\Builder;
use Dvelum\Orm\Record;
use Dvelum\Orm\Model;

class ConfigTest extends TestCase
{
    public function testGetObjectTtile()
    {
        $cfg = Record\Config::factory('User');
        $oldTitle = $cfg->getTitle();
        $cfg->setObjectTitle('My title');
        $this->assertEquals($cfg->getTitle(), 'My title');
        $cfg->setObjectTitle($oldTitle);
    }

    public function testCanUseForeignKeys()
    {
        $keyManager = new Record\Config\ForeignKey();
        $cfg = Record\Config::factory('User');
        $this->assertTrue($keyManager->canUseForeignKeys($cfg));

        $cfg = Record\Config::factory('Historylog');
        $this->assertFalse($keyManager->canUseForeignKeys($cfg));
    }

    public function testGetFields()
    {
        $cfg = Record\Config::factory('User');
        $fields = $cfg->getFields();
        $this->assertArrayHasKey('id', $fields);
        $this->assertTrue($fields['id'] instanceof Record\Config\Field);
    }

    public function testGetLinks()
    {
        $cfg = Record\Config::factory('User_Auth');
        $links = $cfg->getLinks();
        $this->assertTrue(isset($links['user']['user']));
    }

    public function  testHasDbPrefix()
    {
        $cfg = Record\Config::factory('User');
        $this->assertTrue($cfg->hasDbPrefix());
    }

    public function testGetValidator()
    {
        $cfg = Record\Config::factory('User');
        $this->assertFalse($cfg->getValidator('id'));
    }

    public function testToArray()
    {
        $cfg = Record\Config::factory('User');
        $array = $cfg->__toArray();
        $this->assertTrue(is_array($array));
        $this->assertTrue(isset($array['fields']));
        $data = $cfg->getData();
        $this->assertEquals($array, $data);
        $data['title'] = 'title 1';
        $cfg->setData($data);
        $this->assertEquals($cfg->getTitle(), 'title 1');
    }


    public function testIsReadOnly()
    {
        $cfg = Record\Config::factory('User');
        $this->assertFalse($cfg->isReadOnly());
    }

    public function testIsLocked()
    {
        $cfg = Record\Config::factory('User');
        $this->assertFalse($cfg->isLocked());
    }

    public function testIsTransact()
    {
        $cfg = Record\Config::factory('User');
        $this->assertTrue($cfg->isTransact());
        $cfg = Record\Config::factory('bgtask_signal');
        $this->assertFalse($cfg->isTransact());
    }

    public function testSave()
    {
        $cfg = Record\Config::factory('User');
        $oldTitle = $cfg->getTitle();
        $cfg->setObjectTitle('My title');
        $this->assertTrue($cfg->save());
        $cfg->setObjectTitle($oldTitle);
        $this->assertTrue($cfg->save());
    }

    public function testRemoveField()
    {
        $cfg = Record\Config::factory('User');
        $fldCfg = $cfg->getFieldConfig('name');

        $fieldManager = new Record\Config\FieldManager();
        $fieldManager->removeField($cfg, 'name');

        $this->assertFalse($cfg->fieldExists('v'));
        $fieldManager->setFieldConfig($cfg, 'name', $fldCfg);
        $this->assertTrue($cfg->fieldExists('name'));
    }

    public function testIsText()
    {
        $cfg = Record\Config::factory('User_Auth');
        $this->assertTrue($cfg->getField('config')->isText());
        $this->assertFalse($cfg->getField('id')->isText());
    }

    public function testIndexExists()
    {
        $cfg = Record\Config::factory('User');
        $indexManager = new Record\Config\IndexManager;

        $this->assertTrue($indexManager->indexExists($cfg, 'PRIMARY'));
        $this->assertFalse($indexManager->indexExists($cfg, 'undefinedindex'));
    }

    public function testIsUnique()
    {
        $cfg = Record\Config::factory('User');
        $this->assertTrue($cfg->getField('id')->isUnique());
        $this->assertTrue($cfg->getField('login')->isUnique());
        $this->assertFalse($cfg->getField('name')->isUnique());
    }

    public function testIsHtml()
    {
        $cfg = Record\Config::factory('User_Auth');
        $this->assertTrue($cfg->getField('config')->isHtml());
        $this->assertFalse($cfg->getField('id')->isHtml());
    }

    public function testIsNumeric()
    {
        $cfg = Record\Config::factory('User');
        $this->assertTrue($cfg->getField('id')->isNumeric());
        $this->assertFalse($cfg->getField('name')->isNumeric());
    }

    public function testIsInteger()
    {
        $cfg = Record\Config::factory('User');
        $this->assertTrue($cfg->getField('id')->isInteger());
        $this->assertFalse($cfg->getField('name')->isInteger());
    }

    public function testIsSearch()
    {
        $cfg = Record\Config::factory('User');
        $this->assertTrue($cfg->getField('id')->isSearch());
        $this->assertTrue($cfg->getField('name')->isSearch());
    }

    public function testGetLinkTittle()
    {
        $cfg = Record\Config::factory('User');
        $this->assertEquals($cfg->getLinkTitle(), 'name');
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

        $cfg = Record\Config::factory('Page');
        $this->assertTrue($cfg->isSystem());

        $this->assertTrue($cfg->getField('id')->isSystem());
        $this->assertFalse($cfg->getField('code')->isSystem());
    }

    public function testgetLinkTitle()
    {
        $cfg = Record\Config::factory('test');
        $this->assertEquals($cfg->getLinkTitle(), $cfg->getPrimaryKey());
    }

    public function testgetDbType()
    {
        $cfg = Record\Config::factory('test');
        $this->assertEquals('bigint', $cfg->getField($cfg->getPrimaryKey())->getDbType());
        $this->assertEquals('float', $cfg->getField('float')->getDbType());
    }

    public function testHasHistory()
    {
        $cfg = Record\Config::factory('User');
        $this->assertTrue($cfg->hasHistory());
        $this->assertFalse($cfg->hasExtendedHistory());
        $cfg = Record\Config::factory('Historylog');
        $this->assertFalse($cfg->hasHistory());
    }

    public function testIsObjectLink()
    {
        $cfg = Record\Config::factory('test');
        $this->assertTrue($cfg->getField('link')->isObjectLink());
        $this->assertFalse($cfg->getField('multilink')->isObjectLink());
        $this->assertFalse($cfg->getField('integer')->isObjectLink());
        $this->assertFalse($cfg->getField('dictionary')->isObjectLink());
    }

    public function testIsMultiLink()
    {
        $cfg = Record\Config::factory('test');
        $this->assertTrue($cfg->getField('multilink')->isMultiLink());
        $this->assertFalse($cfg->getField('link')->isMultiLink());
        $this->assertFalse($cfg->getField('dictionary')->isMultiLink());
        $this->assertFalse($cfg->getField('integer')->isMultiLink());
    }

    public function testGetLinkedObject()
    {
        $cfg = Record\Config::factory('test');
        $this->assertEquals('user', $cfg->getField('link')->getLinkedObject());
        $this->assertEquals('page', $cfg->getField('multilink')->getLinkedObject());
    }

    public function testGetLinkedDictionary()
    {
        $cfg = Record\Config::factory('test');
        $this->assertEquals('link_type', $cfg->getField('dictionary')->getLinkedDictionary());
    }

    public function testGetSearchFields()
    {
        $cfg = Record\Config::factory('test');
        $searchFields = $cfg->getSearchFields();
        $this->assertEquals(2, sizeof($searchFields));
        $this->assertTrue(in_array('id', $searchFields, true));
        $this->assertTrue(in_array('varchar', $searchFields, true));
    }

    public function testIsRevControl()
    {
        $cfg = Record\Config::factory('test');
        $this->assertFalse($cfg->isRevControl());

        $cfg = Record\Config::factory('User');
        $this->assertFalse($cfg->isRevControl());
    }

    public function testIsSystemField()
    {
        $cfg = Record\Config::factory('test');
        $this->assertFalse($cfg->getField('varchar')->isSystem());
        $this->assertTrue($cfg->getField('id')->isSystem());

        $cfg = Record\Config::factory('User');
        $this->assertTrue($cfg->getField('id')->isSystem());
    }

    public function testGetForeignKeys()
    {
        $cfg = Record\Config::factory('User_auth');
        $keyManager = new Record\Config\ForeignKey();
        $keys = $keyManager->getForeignKeys($cfg);
        $keys = \Dvelum\Utils::rekey('curField', $keys);
        $this->assertTrue(isset($keys['user']));
        $this->assertFalse(isset($keys['config']));
    }


    public function testIsVcField()
    {
        $cfg = Record\Config::factory('test');
        $this->assertTrue($cfg->isVcField('author_id'));
        $this->assertFalse($cfg->isVcField('id'));
    }

    public function testHasManyToMany()
    {
        $cfg = Record\Config::factory('test');
        $relation = new Record\Config\Relation();
        $this->assertFalse($relation->hasManyToMany($cfg));
    }
}
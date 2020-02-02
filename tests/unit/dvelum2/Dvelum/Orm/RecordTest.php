<?php

use PHPUnit\Framework\TestCase;

use Dvelum\Orm;
use Dvelum\Orm\Record;

class RecordTest extends TestCase
{
    /**
     * @return Orm\RecordInterface|Orm\RecordInterface
     * @throws Exception
     */
    protected function createObject()
    {
        $object = Record::factory('User_Auth');
        return $object;
    }

    public function testSetId()
    {
        $object = $this->createObject();
        $object->setId(10);
        $this->assertEquals(10, $object->getId());
    }

    public function testSetInsertId()
    {
        $object = $this->createObject();
        $object->setInsertId(123);
        $this->assertEquals(123, $object->getInsertId());
    }

    public function  testSetVersion()
    {
        $object = $this->createObject();
        $object->setVersion(2);
        $this->assertEquals(2, $object->getVersion());
    }

    public function testGetFields()
    {
        $object = $this->createObject();
        $fields = $object->getFields();
        $this->assertTrue(!empty($fields));
        foreach ($fields as $field){
            $field = $object->getConfig()->getField($field);
            $this->assertTrue($field instanceof  Dvelum\Orm\Record\Config\Field);
        }
    }

    public function testHasUpdates()
    {
        $object = $this->createObject();
        $this->assertFalse($object->hasUpdates());
        $object->set('config' ,'123');
        $this->assertTrue($object->hasUpdates());
    }

    public function testGetUpdates()
    {
        $object = $this->createObject();
        $object->set('config' ,'123');
        $this->assertEquals(['config'=>'123'],$object->getUpdates());
    }

    public function testCommitChanges()
    {
        $object = $this->createObject();
        $object->commitChanges();
        $this->assertFalse($object->hasUpdates());
        $object->set('config' ,'123');
        $object->commitChanges();
        $this->assertFalse($object->hasUpdates());
    }

    public function testFieldExist()
    {
        $object = $this->createObject();
        $this->assertFalse($object->fieldExists('name_name'));
        $this->assertTrue($object->fieldExists('id'));
    }

    public function testGetLinkedObject()
    {
        $object = $this->createObject();
        $this->assertEquals('user', $object->getLinkedObject('user'));
    }

    public function testSetValues()
    {
        $object = $this->createObject();
        $values = [
            'config'=>'my_code',
        ];
        $object->setValues($values);
        $this->assertEquals($values, $object->getUpdates());
        $this->assertEquals('my_code', $object->get('config'));
    }

    public function testGetOld()
    {
        $object = $this->createObject();
        $object->set('config','1');
        $object->commitChanges();
        $object->set('config','2');
        $this->assertEquals('1', $object->getOld('config'));
    }

    public function testAddErrorMessage()
    {
        $object = $this->createObject();
        $object->addErrorMessage('msg');
        $this->assertEquals('msg',$object->getErrors()[0]);
    }

    public function testToString()
    {
        $object = $this->createObject();
        $object->setId(1);
        $this->assertEquals('1', $object->__toString());
    }

    public function testRejectChanges()
    {
        $object = $this->createObject();
        $values = [
            'config'=>'my_code'
        ];
        $object->setValues($values);
        $object->rejectChanges();
        $this->assertTrue(empty($object->getUpdates()));
    }

    public function testInstanceOf()
    {
        $object = $this->createObject();
        $this->assertTrue($object->isInstanceOf('User_Auth'));
    }

    public function testSet()
    {
        $object = $this->createObject();
        $object->set('config' ,'pageCode');
        $this->assertEquals('pageCode', $object->get('config'));
        $object->setId(23);
        $this->assertEquals(23, $object->getId());
    }

    public function testGetDataModel()
    {
        $object = $this->createObject();
        $this->assertTrue($object->getDataModel() instanceof Record\DataModel);
    }

}
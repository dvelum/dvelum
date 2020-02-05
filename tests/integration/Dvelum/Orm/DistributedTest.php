<?php

use PHPUnit\Framework\TestCase;
use Dvelum\Orm\Model;
use Dvelum\Orm\Record;

class DistributedTest extends TestCase
{
    public function testRoutes()
    {
        /**
         * @var \Dvelum\Orm\Distributed\Record $object
         */
        $object = Record::factory('test_sharding');
        $code = uniqid('', true);

        $object->setValues([
            'code' => $code,
            'title' => 'Title'
        ]);
        $this->assertTrue((bool)$object->save());
        /**
         * @var \Dvelum\Orm\Distributed\Record $objectItem
         */
        $objectItem = Record::factory('test_sharding_item');
        $objectItem->setValues([
            'test_sharding' => $object->getId(),
            'value' => time()
        ]);
        $saved = $objectItem->save();
        $this->assertTrue((bool)$saved );
        $this->assertEquals($object->get('shard'), $objectItem->get('shard'));

        $record2 = Record::factory('test_sharding_item', $objectItem->getId(), $objectItem->get('shard'));
        $this->assertEquals($objectItem->get('value'), $record2->get('value'));

        $record2->set('value', 7);
        $object->set('title', 'title 2');

        $this->assertTrue((bool)$object->save());
        $this->assertTrue((bool)$record2->save());

        $this->assertTrue($objectItem->delete());
        $this->assertTrue($object->delete());
    }

    public function testVirtualBucket()
    {
        $object = Record::factory('test_sharding_bucket');
        $object->setInsertId(1);
        $object->set('value', 1);

        $object2 = Record::factory('test_sharding_bucket');
        $object2->setInsertId(2);
        $object2->set('value', 2);

        $object3 = Record::factory('test_sharding_bucket');
        $object3->setInsertId(20000);
        $object3->set('value', 3);

        $this->assertTrue((bool)$object->save());
        $this->assertTrue((bool)$object2->save());
        $this->assertTrue((bool)$object3->save());

        $this->assertEquals($object->get('bucket'), $object2->get('bucket'));
        $this->assertEquals($object->get('shard'), $object2->get('shard'));
        $this->assertTrue($object->get('bucket')!==$object3->get('bucket'));

        $loaded = Record::factory('test_sharding_bucket', $object->getId(), $object->get('shard'));
        $this->assertEquals($loaded->get('value'), $object->get('value'));

        $this->assertTrue($object->delete());
        $this->assertTrue($object2->delete());
        $this->assertTrue($object3->delete());
    }
}
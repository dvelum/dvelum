<?php

use PHPUnit\Framework\TestCase;
use Dvelum\Orm\Model;
use Dvelum\Orm\Record;

class DistributedTest extends TestCase
{
    /**
     * @return RecordInterface[]
     * @throws \Dvelum\Orm\Exception
     */
    public function createBucketObjects():array
    {
        $result = [];
        $object = Record::factory('test_sharding_bucket');
        $object->setInsertId(1);
        $object->set('value', 1);
        $this->assertTrue(!empty($object->save()));
        $result[$object->getId()] = $object;

        $object2 = Record::factory('test_sharding_bucket');
        $object2->setInsertId(2);
        $object2->set('value', 2);
        $this->assertTrue(!empty($object2->save()));
        $result[$object2->getId()] = $object2;

        $object3 = Record::factory('test_sharding_bucket');
        $object3->setInsertId(20000);
        $object3->set('value', 3);
        $this->assertTrue(!empty($object3->save()));
        $result[$object3->getId()] = $object3;

        $object4 = Record::factory('test_sharding_bucket');
        $object4->setInsertId(50000);
        $object4->set('value', 4);
        $this->assertTrue(!empty($object4->save()));
        $result[$object4->getId()] = $object4;

        $this->assertEquals($object->get('bucket'), $object2->get('bucket'));
        $this->assertEquals($object->get('shard'), $object2->get('shard'));
        $this->assertTrue($object->get('bucket')!==$object3->get('bucket'));

        $loaded = Record::factory('test_sharding_bucket', $object->getId(), $object->get('shard'));
        $this->assertEquals($loaded->get('value'), $object->get('value'));

        return $result;
    }

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
        $objects = $this->createBucketObjects();
        $distributed = \Dvelum\Orm\Distributed::factory();
        $shards = $distributed->findObjectsShards('test_sharding_bucket' , array_keys($objects));

        foreach ($shards as $shard => $objectIdLsi){
            foreach ($objectIdLsi as $objectId)
                $this->assertEquals($objects[$objectId]->get('shard'), $shard);
        }

        foreach ($objects as $object){
            $this->assertTrue($object->delete());
        }
    }
}
<?php
declare(strict_types=1);

namespace Dvelum\App\Console\Orm\Test;

use Dvelum\App\Console;
use Dvelum\Orm\Record;

class OrmShardingKey extends Console\Action
{
    public function action(): bool
    {

        $o = Record::factory('shard_vbu');
        $o->setId(1);
        $o->setValues([
            'articul' => '1111111',
            'maker_id' => 1,
            'description' => 'text 1',
            'uuid' => '70dbb1e9-9f71-11e7-9b70-005056813ef5',
        ]);
        $o->save();

        $o = Record::factory('shard_vbu');
        $o->setId(10);
        $o->setValues([
            'articul' => '1111111',
            'maker_id' => 1,
            'description' => 'text 10',
            'uuid' => '70dbb1e9-9f71-11e7-9b70-005056813ef5',
        ]);
        $o->save();

        $o = Record::factory('shard_vbu');
        $o->setId(21);
        $o->setValues([
            'articul' => '1111111',
            'maker_id' => 1,
            'description' => 'text 21',
            'uuid' => '70dbb1e9-9f71-11e7-9b70-005056813ef5',
        ]);
        $o->save();
        $o = Record::factory('shard_vbu');
        $o->setId(22);
        $o->setValues([
            'articul' => '1111111',
            'maker_id' => 1,
            'description' => 'text 22',
            'uuid' => '70dbb1e9-9f71-11e7-9b70-005056813ef5',
        ]);
        $o->save();

        /*
        $file =  './xaa';
        $struc = [
            'id' =>0,
            'articul'=>1,
            'maker_id'=>2,
            'description'=>4,
            'uuid'=>5
        ];
        $file = fopen($file, 'r');
        while($row = fgetcsv($file)){
            $o = Record::factory('shard_vbu');
            $o->setId($row[$struc['id']]);
            $o->setValues([
               'articul' => $row[$struc['articul']],
               'maker_id' => $row[$struc['maker_id']],
               'description' => $row[$struc['description']],
               'uuid' => $row[$struc['uuid']],
            ]);
            die();
        }
        */
        return true;
    }

    protected function testLoad()
    {
        $record = Record::factory('complex_shard_no_index', 25, 'shard3');

        $record->set('price',107.03);
        $record->save();
        $record = Record::factory('complex_shard_no_index', 25, 'shard3');

    }


    protected function testWrite($object)
    {
        $record = Record::factory($object);
        $record->setValues([
            'price' => 1000,
            'warehouse' => 7001
        ]);
        $record->save();


        $record = Record::factory($object);
        $record->setValues([
            'price' => 1002,
            'warehouse' => 7001
        ]);
        $record->save();

        $record = Record::factory($object);
        $record->setValues([
            'price' => 1002,
            'warehouse' => 7001
        ]);
        $record->save();


        $record = Record::factory($object);
        $record->setValues([
            'price' => 1003,
            'warehouse' => 7002
        ]);
        $record->save();

        $record = Record::factory($object);
        $record->setValues([
            'price' => 1003,
            'warehouse' => 7003
        ]);
        $record->save();
    }
}
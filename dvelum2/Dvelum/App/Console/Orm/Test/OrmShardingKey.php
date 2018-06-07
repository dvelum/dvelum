<?php
declare(strict_types=1);

namespace Dvelum\App\Console\Orm\Test;

use Dvelum\App\Console;
use Dvelum\Orm\Record;

class OrmShardingKey extends Console\Action
{
    public function action(): bool
    {
        $this->testWrite('complex_shard_no_index');
       // $this->testLoad();

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
<?php
declare(strict_types=1);

namespace Dvelum\App\Console\Orm;

use Dvelum\App\Console;
use Dvelum\Config;
use Dvelum\Orm;

class BuildShards extends Console\Action
{
    public function action(): bool
    {
        $ormConfig = Config::storage()->get('orm.php');
        $dbObjectManager = new Orm\Record\Manager();
        $success = true;

        echo 'BUILD SHARDS ' . PHP_EOL;

        $sharding = Config::storage()->get('sharding.php');
        $shardsFile = $sharding->get('shards');
        $shardsConfig = Config::storage()->get($shardsFile);
        $registeredObjects = $dbObjectManager->getRegisteredObjects();

        foreach ($shardsConfig as $item)
        {
            $shardId = $item['id'];
            echo  $shardId . ' ' . PHP_EOL;
            echo "\t Tables" . PHP_EOL;
            //build objects
            foreach ($registeredObjects as $index => $object)
            {
                if (!Orm\Record\Config::factory($object)->isDistributed()) {
                    unset($registeredObjects[$index]);
                    continue;
                }

                echo "\t\t" . $object . ' : ';

                $builder = Orm\Record\Builder::factory($object);
                $builder->setConnection(Orm\Model::factory($object)->getDbShardConnection($shardId));
                if ($builder->build(false, true)) {
                    echo 'OK' . PHP_EOL;
                } else {
                    $success = false;
                    echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors())) . PHP_EOL;
                }
            }
            //build foreign keys
            if ($ormConfig->get('foreign_keys'))
            {
                echo "\t Foreign Keys " . PHP_EOL;

                foreach ($registeredObjects as $index => $object)
                {
                    echo "\t\t" . $object . ' : ';

                    $builder = Orm\Record\Builder::factory($object);
                    $builder->setConnection(Orm\Model::factory($object)->getDbShardConnection($shardId));
                    if ($builder->build(true, true)) {
                        echo 'OK' . PHP_EOL;
                    } else {
                        $success = false;
                        echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors())) . PHP_EOL;
                    }
                }
            }
        }
        return $success;
    }
}
<?php
declare(strict_types=1);

namespace Dvelum\App\Console\Orm;

use Dvelum\App\Console;
use Dvelum\Orm;
use Dvelum\Config;

class Build extends Console\Action
{
    public function action(): bool
    {
        $ormConfig = Config::storage()->get('orm.php');
        $dbObjectManager = new Orm\Record\Manager();
        $success = true;

        echo "BUILD OBJECTS " . PHP_EOL;

        // build object
        foreach ($dbObjectManager->getRegisteredObjects() as $object) {
            $cfg = Orm\Record\Config::factory($object);
            if ($cfg->isDistributed()) {
                echo "\t " . $object . ' :  is distributed, skip' . PHP_EOL;
                continue;
            }
            if ($cfg->isLocked() || $cfg->isReadOnly()) {
                echo "\t " . $object . ' :  is locked or readonly, skip' . PHP_EOL;
                continue;
            }

            echo "\t " . $object . ' : ';
            $builder = Orm\Record\Builder::factory($object);
            if ($builder->build(false)) {
                echo 'OK' . PHP_EOL;
            } else {
                $success = false;
                echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors())) . PHP_EOL;
            }
        }

        //build foreign keys
        if ($ormConfig->get('foreign_keys')) {
            echo PHP_EOL . "\t BUILD FOREIGN KEYS" . PHP_EOL . PHP_EOL;

            foreach ($dbObjectManager->getRegisteredObjects() as $object) {
                $cfg = Orm\Record\Config::factory($object);

                if ($cfg->isDistributed()) {
                    echo "\t " . $object . ' :  is distributed, skip' . PHP_EOL;
                    continue;
                }

                if ($cfg->isLocked() || $cfg->isReadOnly()) {
                    echo "\t " . $object . ' :  is locked or readonly, skip' . PHP_EOL;
                    continue;
                }

                echo "\t " . $object . ' : ';
                $builder = Orm\Record\Builder::factory($object);
                if ($builder->build(true)) {
                    echo 'OK' . PHP_EOL;
                } else {
                    $success = false;
                    echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors())) . PHP_EOL;
                }
            }
        }
        return $success;
    }
}
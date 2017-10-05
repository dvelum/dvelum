<?php
declare(strict_types=1);

namespace Dvelum\App\Console\Orm;
use Dvelum\App\Console;
use Dvelum\Orm;

class Build extends Console\Action
{
    public function action() : bool
    {
        $dbObjectManager = new Orm\Object\Manager();
        $success = true;
        $t = microtime(true);
        echo 'BUILD OBJECTS '. PHP_EOL;
        foreach($dbObjectManager->getRegisteredObjects() as $object)
        {
            echo  $object . ' : ';
            $builder = Orm\Object\Builder::factory($object);
            if($builder->build()){
                echo 'OK' . PHP_EOL;
            }else{
                $success = false;
                echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors())). PHP_EOL;
            }
        }
        return $success;
    }
}
<?php
use Dvelum\Orm;
class Console_Orm_Build extends Console_Action
{
    /**
     * (non-PHPdoc)
     * @see Console_Action::run()
     */
    public function run()
    {
        $dbObjectManager = new Orm\Object\Manager();
        foreach($dbObjectManager->getRegisteredObjects() as $object)
        {
            echo 'build ' . $object . ' : ';
            $builder = Orm\Object\Builder::factory($object);
            if($builder->build()){
                echo 'OK';
            }else{
                echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
            }
            echo "\n";
        }
    }
}
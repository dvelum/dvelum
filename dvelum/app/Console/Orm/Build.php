<?php
class Console_Orm_Build extends Console_Action
{
    /**
     * (non-PHPdoc)
     * @see Console_Action::run()
     */
    public function run()
    {
        $dbObjectManager = new Db_Object_Manager();
        foreach($dbObjectManager->getRegisteredObjects() as $object)
        {
            echo 'build ' . $object . ' : ';
            $builder = new Db_Object_Builder($object);
            if($builder->build()){
                echo 'OK';
            }else{
                echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
            }
            echo "\n";
        }
    }
}
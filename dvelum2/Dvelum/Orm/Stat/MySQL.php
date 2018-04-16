<?php
declare(strict_types=1);

namespace Dvelum\Orm\Stat;

use Dvelum\Orm\Model;

class MySQL
{

    /**
     * Get Database tables info
     * @param Model $model
     * @param ?string $tableName
     * @throws \Exception
     * @return array
     */
    public function getTablesInfo(Model $model, ?string $tableName = null) : array
    {
        $db = $model->getDbConnection();
        try
        {
            /*
             * Getting object db tables info
             */
            if(!empty($tableName)){
                $sql = 'SHOW TABLE STATUS  where `Name` = '.$db->quote($tableName);
                $result =  $db->fetchRow($sql);
                return $result;
            }else{
                return $db->fetchAll("SHOW TABLE STATUS");
            }
        }
        catch (\Exception $e)
        {
            $model->logError($e->getMessage());
            return [];
        }
    }
}
<?php
declare(strict_types=1);

namespace Dvelum\Orm\Stat;

use Dvelum\Orm\Model;
use Dvelum\Db\Adapter;

class MySQL
{
    /**
     * Get Database tables info
     * @param Model $model
     * @param ?string $tableName
     * @throws \Exception
     * @return array
     */
    public function getTablesInfo(Adapter $dbAdapter, ?string $tableName = null) : array
    {
        try
        {
            /*
             * Getting object db tables info
             */
            if(!empty($tableName)){
                $sql = 'SHOW TABLE STATUS  where `Name` = '.$dbAdapter->quote($tableName);
                $result =  $dbAdapter->fetchRow($sql);
                return $result;
            }else{
                return $dbAdapter->fetchAll("SHOW TABLE STATUS");
            }
        }
        catch (\Exception $e)
        {
            return [];
        }
    }
}
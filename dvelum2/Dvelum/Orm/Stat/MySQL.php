<?php
declare(strict_types=1);

namespace Dvelum\Orm\Stat;

use Dvelum\Orm\Model;

class MySQL
{

    /**
     * Get Database tables info
     * @param Model $model
     * @throws \Exception
     * @return array
     */
    public function getTablesInfo(Model $model) : array
    {
        $db = $model->getDbConnection();
        try
        {
            /*
             * Getting object db tables info
             */
            return $db->fetchAll("SHOW TABLE STATUS");
        }
        catch (\Exception $e)
        {
            $model->logError($e->getMessage());
            return [];
        }
    }
}
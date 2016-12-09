<?php
namespace Dvelum\App\Data;

use Dvelum\App;
use Dvelum\Model;
use Dvelum\Orm;

class Api
{
    protected $apiRequest;
    protected $fields;
    protected $user;


    public function __construct(App\Data\Api\Request $request , \User $user)
    {
        $this->apiRequest = $request;
        $this->user = $user;
    }

    public function getList()
    {
        $object = $this->apiRequest->getObject();

        $ormObjectConfig = Orm\Object\Config::factory($object);

        if($ormObjectConfig->isRevControl())
        {
            return Model::factory($object)->getListVc(
                $this->apiRequest->getPagination(),
                $this->apiRequest->getFilters(),
                $this->apiRequest->getQuery(),
                '*',
                'user',
                'updater'
            );
        }
        else
        {
            return Model::factory($object)->getList(
                $this->apiRequest->getPagination(),
                $this->apiRequest->getFilters(),
                '*',
                false,
                $this->apiRequest->getQuery()
            );
        }
    }

    public function getCount()
    {
        $object = $this->apiRequest->getObject();

        return Model::factory($object)->getCount(
            $this->apiRequest->getFilters(),
            $this->apiRequest->getQuery()
        );
    }
}
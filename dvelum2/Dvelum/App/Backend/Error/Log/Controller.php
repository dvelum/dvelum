<?php
namespace Dvelum\App\Backend\Error\Log;

use Dvelum\App;
use Dvelum\App\Controller\Event;
use Dvelum\App\Controller\EventManager;
use Dvelum\Db\Select;

class Controller extends App\Backend\Api\Controller
{
    protected $listFields = ['id','name','date','message'];

    public function getModule(): string
    {
        return  'Error_Log';
    }

    public function getObjectName(): string
    {
        return  'Error_Log';
    }

    public function initListeners()
    {
        $apiRequest = $this->apiRequest;
        $apiRequest->setObjectName($this->getObjectName());

        $this->eventManager->on(EventManager::BEFORE_LIST, function (Event $event) use ($apiRequest) {

            $filter = $apiRequest->getFilters();
            if(isset($filter['date']) && !empty($filter['date'])){
                $date = date('Y-m-d' ,strtotime($filter['date']));
                $filter['date'] =  new Select\Filter(
                    'date',
                    [
                        $date.' 00:00:00', $date.' 23:59:59'
                    ],
                    Select\Filter::BETWEEN
                );
                $apiRequest->addFilter('date', $filter['date']);
            }
        });
    }
}
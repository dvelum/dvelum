<?php
namespace Dvelum\App\Backend\Error\Log;

use Dvelum\App;

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
}
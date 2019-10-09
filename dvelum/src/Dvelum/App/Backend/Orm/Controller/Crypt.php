<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
declare(strict_types=1);
namespace Dvelum\App\Backend\Orm\Controller;

use Dvelum\App\Backend\Controller;
use Dvelum\BackgroundTask\Manager;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Store\Factory;


class Crypt extends Controller
{
    protected $encryptContainerPrefix = 'encrypt_';
    protected $decryptContainerPrefix = 'decrypt_';

    public function getModule(): string
    {
        return 'Orm';
    }

    public function indexAction()
    {
    }

    /**
     * Decrypt object data (background)
     */
    public function decryptAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }
        $object = $this->request->post('object' , 'string' , false);

        if(!$object || !Orm\Record\Config::configExists($object)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $container = $this->decryptContainerPrefix . $object;

        //$objectModel = Model::factory($object);
        $taskModel = Model::factory('bgtask');
        $signalModel = Model::factory('Bgtask_Signal');

        //disable profiling in dev mode
       // if($this->appConfig->get('development')) {
            //$taskModel->getDbConnection()->getProfiler()->setEnabled(false);
            //$signalModel->getDbConnection()->getProfiler()->setEnabled(false);
            //$objectModel->getDbConnection()->getProfiler()->setEnabled(false);
       // }

        $logger =  new \Dvelum\BackgroundTask\Log\File($this->appConfig['task_log_path'] . $container .'_' . date('d_m_Y__H_i_s'));
        $bgStorage = new  \Dvelum\BackgroundTask\Storage\Orm($taskModel , $signalModel);
        $tm = Manager::factory();
        $tm->setStorage($bgStorage);
        $tm->setLogger($logger);

        // Start encryption task
        $tm->launch(
            Manager::LAUNCHER_SIMPLE,
            '\\Dvelum\\App\\Task\\Orm\\Decrypt' ,
            [
                'object'=>$object,
                'session_container'=>$container
            ]
        );
    }

    /**
     * Encrypt object data (background)
     */
    public function encryptAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $object = $this->request->post('object' , 'string' , false);

        if(!$object || !Orm\Record\Config::configExists($object)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $container = $this->encryptContainerPrefix . $object;

        //$objectModel = Model::factory($object);
        $taskModel = Model::factory('bgtask');
        $signalModel = Model::factory('Bgtask_Signal');

        //disable profiling in dev mode
        //if($this->appConfig->get('development')) {
//            $taskModel->getDbConnection()->getProfiler()->setEnabled(false);
//            $signalModel->getDbConnection()->getProfiler()->setEnabled(false);
//            $objectModel->getDbConnection()->getProfiler()->setEnabled(false);
      //  }

        $logger =  new \Dvelum\BackgroundTask\Log\File($this->appConfig['task_log_path'] . $container .'_' . date('d_m_Y__H_i_s'));
        $bgStorage = new \Dvelum\BackgroundTask\Storage\Orm($taskModel , $signalModel);
        $tm = Manager::factory();
        $tm->setStorage($bgStorage);
        $tm->setLogger($logger);

        // Start encryption task
        $tm->launch(
            Manager::LAUNCHER_SIMPLE,
            '\\Dvelum\\App\\Task\\Orm\\Encrypt' ,
             [
                'object'=>$object,
                'session_container'=>$container
             ]
        );
    }

    /**
     * Check background process status
     */
    public function taskStatAction()
    {
        $object = $this->request->post('object' , 'string' , false);
        $type = $this->request->post('type' , 'string' , false);

        if(!$object || ! $type) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        switch($type){
            case 'encrypt':
                $container = $this->encryptContainerPrefix . $object;
                break;
            case 'decrypt':
                $container = $this->decryptContainerPrefix . $object;
                break;
            default:
                $this->response->error($this->lang->get('WRONG_REQUEST'));
                return;
        }

        $session = Factory::get(Factory::SESSION);

        if(!$session->keyExists($container)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $pid = $session->get($container);
        $taskModel = Model::factory('bgtask');
        $statusData = $taskModel->getItem($pid);

        if(empty($statusData)){
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }

        $this->response->success([
            'status' =>  $statusData['status'],
            'op_total' =>  $statusData['op_total'],
            'op_finished' =>  $statusData['op_finished']
        ]);
    }
}
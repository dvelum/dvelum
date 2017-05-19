<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
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

namespace Dvelum\App\Backend\Orm\Controller;

use Dvelum\App\Backend\Controller;
use Dvelum\App\Backend\Orm\Manager;
use Dvelum\Config;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Lang;
use Dvelum\View;
use Dvelum\Template;

class Crypt extends Controller
{
    protected $encryptContainerPrefix = 'encrypt_';
    protected $decryptContainerPrefix = 'decrypt_';

    public function getModule()
    {
        return 'Orm';
    }

    public function indexAction()
    {
    }

    /**
     * Decrypt object data (background)
     */
    public function decryptDataAction()
    {
        $this->checkCanEdit();
        $object = $this->request->post('object' , 'string' , false);

        if(!$object || !Orm\Object\Config::configExists($object)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $container = $this->decryptContainerPrefix . $object;

        $objectModel = Model::factory($object);
        $taskModel = Model::factory('bgtask');
        $signalModel = Model::factory('Bgtask_Signal');

        //disable profiling in dev mode
        if($this->appConfig->get('development')) {
            $taskModel->getDbConnection()->getProfiler()->setEnabled(false);
            $signalModel->getDbConnection()->getProfiler()->setEnabled(false);
            $objectModel->getDbConnection()->getProfiler()->setEnabled(false);
        }

        $logger =  new Bgtask_Log_File($this->appConfig['task_log_path'] . $container .'_' . date('d_m_Y__H_i_s'));

        $bgStorage = new Bgtask_Storage_Orm($taskModel , $signalModel);
        $tm = Bgtask_Manager::getInstance();
        $tm->setStorage($bgStorage);
        $tm->setLogger($logger);

        // Start encryption task
        $tm->launch(
            Bgtask_Manager::LAUNCHER_SIMPLE,
            'Task_Orm_Decrypt' ,
            array(
                'object'=>$object,
                'session_container'=>$container
            )
        );
    }

    /**
     * Encrypt object data (background)
     */
    public function encryptDataAction()
    {
        $this->checkCanEdit();
        $object = $this->request->post('object' , 'string' , false);

        if(!$object || !Orm\Object\Config::configExists($object)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $container = $this->encryptContainerPrefix . $object;

        $objectModel = Model::factory($object);
        $taskModel = Model::factory('bgtask');
        $signalModel = Model::factory('Bgtask_Signal');

        //disable profiling in dev mode
        if($this->appConfig->get('development')) {
            $taskModel->getDbConnection()->getProfiler()->setEnabled(false);
            $signalModel->getDbConnection()->getProfiler()->setEnabled(false);
            $objectModel->getDbConnection()->getProfiler()->setEnabled(false);
        }

        $logger =  new Bgtask_Log_File($this->appConfig['task_log_path'] . $container .'_' . date('d_m_Y__H_i_s'));

        $bgStorage = new Bgtask_Storage_Orm($taskModel , $signalModel);
        $tm = Bgtask_Manager::getInstance();
        $tm->setStorage($bgStorage);
        $tm->setLogger($logger);

        // Start encryption task
        $tm->launch(
            Bgtask_Manager::LAUNCHER_SIMPLE,
            'Task_Orm_Encrypt' ,
            array(
                'object'=>$object,
                'session_container'=>$container
            )
        );
    }
}
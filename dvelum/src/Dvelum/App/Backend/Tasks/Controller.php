<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2019  Kirill Yegorov
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
 */
declare(strict_types=1);

namespace Dvelum\App\Backend\Tasks;

use Dvelum\App\Backend;

use Dvelum\BackgroundTask\AbstractTask;
use Dvelum\BackgroundTask\Log\File;
use Dvelum\BackgroundTask\Manager;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Config;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Service;

/**
 * Background tasks module UI Controller
 */
class Controller extends Backend\Controller
{
    /**
     * @var Manager
     */
    protected $tManager;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $bgStorage = new  \Dvelum\BackgroundTask\Storage\Orm(Model::factory('bgtask') , Model::factory('Bgtask_Signal'));
        $logger = new File('./.log/test'.date('YmdHis').'.txt');
        $this->tManager = Manager::factory();
        $this->tManager->setStorage($bgStorage);
        $this->tManager->setLogger($logger);
    }

    /**
     * @inheritDoc
     */
    public function getModule(): string
    {
        return 'Tasks';
    }
    
    /**
     * @inheritDoc
     */
    public function getObjectName(): string
    {
        return '';
    }

    public function indexAction()
    {
        $this->resource->addInlineJs('
            var canEdit = '.($this->user->getModuleAcl()->canEdit($this->getModule())).';
            var canDelete = '.($this->user->getModuleAcl()->canDelete($this->getModule())).';
        ');
        $this->resource->addJs('/js/app/system/Tasks.js', 4);
        $this->resource->addJs('/js/app/system/crud/'.strtolower($this->getModule()).'.js', 4);
    }

    /**
     * Get tasks list
     */
    public function listAction()
    {
        $data = $this->tManager->getList();
        /**
         * @var \Dvelum\App\Dictionary\Service $dictionaryService
         */
        $dictionaryService = Service::get('dictionary');

        if(!empty($data)){
            $dict =  $dictionaryService->get('task');
            foreach ($data as $k=>&$v){
                $v['status_code'] = $v['status'];
                if($dict->isValidKey($v['status']))
                    $v['status'] = $dict->getValue($v['status']);

                $finish = strtotime((string)$v['time_finished']);
                if($finish<=0){
                    $finish = time();
                }

                $v['runtime'] = \Dvelum\Utils::formatTime($finish - strtotime($v['time_started']));

                $v['memory'] = \Dvelum\Utils::formatFileSize($v['memory']);
                $v['memory_peak'] = \Dvelum\Utils::formatFileSize($v['memory_peak']);
                if($v['op_total']==0)
                    $v['progress'] = 0;
                else
                    $v['progress'] = round(($v['op_finished']) / $v['op_total'] , 2) * 100 ;
            }unset($v);
        }
        $this->response->success($data);
    }

    /**
     * Launch test task
     */
    public function testAction()
    {
        /**
         * @var \Dvelum\Orm\Orm $service
         */
        $this->tManager->launch(Manager::LAUNCHER_JSON, '\\Dvelum\\App\\Task\\Test' , []);
    }

    /**
     * Kill task by pid
     */
    public function killAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $pid = $this->request->post('pid', 'integer', 0);
        
        if(!$pid){
           $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        if($this->tManager->kill($pid)){
            $this->response->success();
        }
        else{
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }
    }

    /**
     * Pause task by pid
     */
    public function pauseAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $pid = $this->request->post('pid', 'integer', 0);
        if(!$pid){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        if($this->tManager->signal($pid, AbstractTask::SIGNAL_SLEEP))
            $this->response->success();
        else
           $this->response->error($this->lang->get('CANT_EXEC'));
    }

    /**
     * Resume task by pid
     */
    public function resumeAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $pid = $this->request->post('pid', 'integer', 0);
        if(!$pid){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        if($this->tManager->signal($pid, AbstractTask::SIGNAL_CONTINUE))
            $this->response->success();
        else
           $this->response->error($this->lang->get('CANT_EXEC'));
    }

    /**
     * Stop task by pid
     */
    public function stopAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $pid = $this->request->post('pid', 'integer', 0);
        if(!$pid){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        if($this->tManager->signal($pid, AbstractTask::SIGNAL_STOP))
            $this->response->success();
        else
           $this->response->error($this->lang->get('CANT_EXEC'));
    }

    /**
     * Get desktop module info
     */
    public function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] =  '/js/app/system/Tasks.js';
        /*
         * Module bootstrap
         */
        if(file_exists($this->appConfig->get('jsPath').'app/system/desktop/' . strtolower($this->getModule()) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->getModule()) .'.js';

        return $projectData;
    }
}
<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
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
 */

use Dvelum\Config;
use Dvelum\Orm\Model;

class Backend_Blocks_Controller extends Backend_Controller_Crud_Vc
{
    /**
     * (non-PHPdoc)
     *
     * @see Backend_Controller::indexAction()
     */
    public function indexAction()
    {
        $this->_resource->addJs('/js/app/system/Blocks.js' , true , 1);
        parent::indexAction();
    }

    public function listAction()
    {           	
        $pager = Request::post('pager', 'array', []);
        $query = Request::post('search', 'string', null);
     
        $result = ['success'=>true, 'count'=>0, 'data'=>[]];

        /**
         * @var Model_Blocks $dataModel
         */
        $dataModel = Model::factory('Blocks');
        /**
         * @var Model_Vc $vc
         */
        $vc = Model::factory('Vc');
        
        $fields = [
            'id' ,
            'title',
            'date_created',
            'published' , 
            'published_version',
            'date_updated',
        	'is_system',
        	'sys_name',
        	'params'
        ];

        $filters = [];

        if($this->_user->getModuleAcl()->onlyOwnRecords($this->_module)){
            $filters['author_id'] = $this->_user->getId();
        }

        $dataQuery = $dataModel->query()
                                ->params($pager)
                                ->filters($filters)
                                ->search($query)
                                ->fields($fields);

        $dataCount = $dataQuery->getCount();

        if(!$dataCount){
            $this->response->success($result);
            return;
        }

        $data = $dataQuery->fetchAll();

        $this->addLinkedInfo(
            $dataModel->getObjectConfig(),
            [
                'user' => 'author_id',
                'updater'=>  'editor_id'
            ],
            $data,
            $dataModel->getPrimaryKey()
        );

        $ids = array(); 
        foreach ($data as $k=>$v)
            $ids[] = $v['id'];
           
        $maxRevisions = $vc->getLastVersion('blocks', $ids);
        
        foreach ($data as $k=>&$v)
        {
            if(isset($maxRevisions[$v['id']]))
                $v['last_version'] = $maxRevisions[$v['id']];
            else
                $v['last_version'] = 0;   
        } 
        unset($v);
        
        $result = array(
            'success'=>true,
            'count'=>$dataCount,
            'data'=>$data
        );

        $this->response->json($result);
    }
    
 	/**
     * List defined Blocks
     */
    public function classListAction()
    {	
    	$blocksPath = $this->_configMain['blocks'];
        $filePath = Config::storage()->get('autoloader.php');
        $filePath = $filePath['paths'];

        $classes = [];
        foreach($filePath as $path)
        {
            if(is_dir($path.'/'.$blocksPath))
            {
                $files = File::scanFiles($path.'/'.$blocksPath , array('.php'), true , File::Files_Only);
                foreach ($files as $k=>$file)
                {
                    $class = Utils::classFromPath(str_replace($path, '',$file));
                    if($class != 'Block_Abstract')
                        $classes[$class] = ['id'=>$class,'title'=>$class];
                }
            }
        }
    	Response::jsonSuccess(array_values($classes));
    }
    
    /**
     * Get list of accepted menu
     */
    public function menulistAction()
    {
    	$menuModel = Model::factory('menu');
    	$fields = ['id', 'title'];
    	$list = $menuModel->query()->fields($fields)->fetchAll();
    	
    	if(!empty($list))
    		$list = array_values($list);
    	
    	Response::jsonSuccess($list);
    }


    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] =  '/js/app/system/Blocks.js';
        /*
         * Module bootstrap
         */
        if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}
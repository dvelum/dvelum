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
declare(strict_types=1);

namespace Dvelum\App\Backend\Menu;

use Dvelum\App\Backend;
use Dvelum\Orm;
use Dvelum\Orm\Model;

class Controller extends Backend\Ui\Controller
{

    public function getModule(): string
    {
        return 'Menu';
    }

    public function getObjectName(): string
    {
        return 'Menu';
    }

    /**
	  * (non-PHPdoc)
	  * @see Backend_Controller_Crud::indexAction()
	  */
	 public function indexAction()
     {   	
        $this->resource->addInlineJs('
        	var canEdit = '.($this->checkCanEdit()).';
        	var canDelete = '.($this->checkCanDelete()).';
        	var menuItemlinkTypes = '.\Dictionary::factory('link_type')->__toJs().';
        ');
        
        Model::factory('Medialib')->includeScripts();

        $this->resource->addJs('/js/app/system/SearchPanel.js', 0);          
        $this->resource->addJs('/js/app/system/HistoryPanel.js', 0);
        $this->resource->addJs('/js/app/system/EditWindow.js' , 0);

         $this->resource->addJs('/js/app/system/Menu.js' , 3);
         $this->resource->addJs('/js/app/system/crud/menu.js', 4);
    } 
    
	/**
	 * (non-PHPdoc)
	 * @see Backend_Controller_Crud::listAction()
	 */
	public function listAction()
	{		
		$data = Model::factory('Menu')->query()
            ->params([
                'sort' => 'title',
                'dir' => 'ASC'
            ])
            ->fields(['id','code','title'])
            ->fetchAll();

		$this->response->success($data);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Backend_Controller_Crud::loaddataAction()
	 */
    public function loaddataAction()
    { 	
        $id = $this->request->post('id', 'integer', false);
        
        if(!$id)
            $this->response->success(array());
              
        try{
            /**
             * @var Orm\ObjectInterface $obj
             */
            $obj = Orm\Object::factory($this->getObjectName(), $id);
        }catch(\Exception $e){
            $this->response->error($this->lang->get('CANT_EXEC'));
        }

        $data = $obj->getData();
                
        /*
         * Prepare  multi link properties
         */      
        $fields = $obj->getFields();

        foreach($fields as $field)
        {
            if($field=='id' || empty($data[$field])){
                continue;
            }

            $objectField = $obj->getConfig()->getField($field);

            if($objectField->isObjectLink() || $objectField->isMultiLink()){
                $linkObject = $obj->getLinkedObject($field);
                $data[$field] = array_values($this->collectLinksData($data[$field], $obj, $linkObject));
            }
        }
        $data['id'] = $obj->getId();
        
        $menuItemModel = Model::factory('menu_item');
        $data['data'] = $menuItemModel->getTreeList($data['id']);
        /*
         * Send response
         */
        $this->response->success($data);
    }
    
    /**
     * Get page list for combobox
     */
    public function pagelistAction()
    {
    	 $pagesModel = Model::factory('Page');
    	 $data = $pagesModel->query()->fields(['id','title'=>'page_title'])->fetchAll();
    	 $this->response->success($data);   	 
    }
    
 	/**
 	 * (non-PHPdoc)
 	 * @see Backend_Controller_Crud::insertObject()
 	 */
    public function insertObject(Orm\Object $object)
    {  
         if(!$recId = $object->save())
             $this->response->error($this->lang->get('CANT_CREATE'));
             
         $linksData = $this->request->post('data', 'raw', false);

         if(strlen($linksData)){
         	$linksData = json_decode($linksData , true);
         } else{
         	$linksData =array();
         }
         
         $menuModel = Model::factory('menu_item');
         
         if(!$menuModel->updateLinks($object->getId(), $linksData))
         	$this->response->error($this->lang->get('CANT_CREATE'));
         	       
         $this->response->success(array('id'=>$recId,));    
    }
    
    /**
     * (non-PHPdoc)
     * @see Backend_Controller_Crud::updateObject()
     */
    public function updateObject(Orm\Object $object)
    {                            
        if(!$object->save())
           $this->response->error($this->lang->get('CANT_EXEC')); 
             	  
        $linksData = $this->request->post('data', 'raw', false);

        if(strlen($linksData))
         	$linksData = json_decode($linksData , true);
        else
         	$linksData =array();

        $menuModel = Model::factory('Menu_Item');
        
        if(!$menuModel->updateLinks($object->getId(), $linksData))
         	$this->response->error($this->lang->get('CANT_CREATE'));   
                 
        $this->response->success(array('id'=>$object->getId()));          
    }
    /**
     * Import Site structure
     */
    public function importAction()
    {
    	$this->response->success(Model::factory('menu_item')->exportsiteStructure());
    }


    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] =  '/js/app/system/Menu.js';
        /*
         * Module bootstrap
         */
        if(file_exists($this->appConfig->get('jsPath').'app/system/desktop/' . strtolower($this->getModule()) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->getModule()) .'.js';

        return $projectData;
    }
}
<?php
class Backend_Menu_Controller extends Backend_Controller_Crud
{
	 /**
	  * (non-PHPdoc)
	  * @see Backend_Controller_Crud::indexAction()
	  */
	 public function indexAction()
     {   	
        $this->_resource->addInlineJs('
        	var canEdit = '.($this->_user->canEdit($this->_module)).';
        	var canDelete = '.($this->_user->canDelete($this->_module)).';
        	var menuItemlinkTypes = '.Dictionary::getInstance('link_type')->__toJs().';
        ');
        
        $modulesConfig = Config::factory(Config::File_Array , $this->_configMain->get('backend_modules'));
       
        Model::factory('Medialib')->includeScripts();

        $this->_resource->addJs('/js/app/system/SearchPanel.js', 0);          
        $this->_resource->addJs('/js/app/system/HistoryPanel.js', 0);
        $this->_resource->addJs('/js/app/system/EditWindow.js' , 0);

         $this->_resource->addJs('/js/app/system/Menu.js' , 3);
         $this->_resource->addJs('/js/app/system/crud/menu.js', 4);
    } 
    
	/**
	 * (non-PHPdoc)
	 * @see Backend_Controller_Crud::listAction()
	 */
	public function listAction()
	{		
		$data = Model::factory('Menu')->getList(
			array(
				'sort' => 'title',
				'dir' => 'ASC'
			),
			false,
			array('id','code','title')
		);
		Response::jsonSuccess($data);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Backend_Controller_Crud::loaddataAction()
	 */
    public function loaddataAction()
    { 	
        $id = Request::post('id', 'integer', false);
        
        if(!$id)
            Response::jsonSuccess(array());
              
        try{
            $obj = new Db_Object($this->_objectName, $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->CANT_EXEC);
        }
               
        $data = $obj->getData();
                
        /*
         * Prepare  mltilink properties
         */      
        $fields = $obj->getFields();
        foreach($fields as $field){
            if($field=='id' || empty($data[$field]))
                continue;
            $linkObject = $obj->getLinkedObject($field);
            if($linkObject !== false)
                $data[$field] = array_values($this->_collectLinksData($data[$field] ,$linkObject));
        } 
        $data['id'] = $obj->getId();
        
        $menuItemModel = Model::factory('menu_item');
        $data['data'] = $menuItemModel->getTreeList($data['id']);
        /*
         * Send response
         */
        Response::jsonSuccess($data);
    }
    
    /**
     * Get page list for combobox
     */
    public function pagelistAction()
    {
    	 $pagesModel = Model::factory('Page');
    	 $data = $pagesModel->getList(false,false,array('id','title'=>'page_title'));
    	 if(empty($data))
    	 	$data = array();
    	 Response::jsonSuccess($data);   	 
    }
    
 	/**
 	 * (non-PHPdoc)
 	 * @see Backend_Controller_Crud::insertObject()
 	 */
    public function insertObject(Db_Object $object)
    {  
         if(!$recId = $object->save())
             Response::jsonError($this->_lang->CANT_CREATE);
             
         $linksData = Request::post('data', 'raw', false);

         if(strlen($linksData)){
         	$linksData = json_decode($linksData , true);
         } else{
         	$linksData =array();
         }
         
         $menuModel = Model::factory('menu_item');
         
         if(!$menuModel->updateLinks($object->getId(), $linksData))
         	Response::jsonError($this->_lang->CANT_CREATE);
         	       
         Response::jsonSuccess(array('id'=>$recId,));    
    }
    
    /**
     * (non-PHPdoc)
     * @see Backend_Controller_Crud::updateObject()
     */
    public function updateObject(Db_Object $object)
    {                            
        if(!$object->save())
           Response::jsonError($this->_lang->CANT_EXEC); 
             	  
        $linksData = Request::post('data', 'raw', false);

        if(strlen($linksData))
         	$linksData = json_decode($linksData , true);
        else
         	$linksData =array();

        $menuModel = Model::factory('Menu_Item');
        
        if(!$menuModel->updateLinks($object->getId(), $linksData))
         	Response::jsonError($this->_lang->CANT_CREATE);   
                 
        Response::jsonSuccess(array('id'=>$object->getId()));          
    }
    /**
     * Import Site structure
     */
    public function importAction()
    {
    	Response::jsonSuccess(Model::factory('menu_item')->exportsiteStructure());
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
        if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}
<?php

class Modules_Generator
{
   /**
    * @var Config_Abstract
    */
   protected $designerConfig;
   /**
    * @var Config_Abstract
    */
   protected $appConfig;

   public $tabTypes = array('Component_Field_System_Medialibhtml' , 'Component_Field_System_Related', 'Component_Field_System_Objectslist');

   public function __construct(){
        $this->appConfig = Registry::get('main' , 'config');
        $this->designerConfig = Config::storage()->get('designer.php');
        Request::setDelimiter($this->appConfig->get('urlDelimiter'));
   }

  /**
   * Create controller file
   * @param string $dir - controller dirrectory
   * @param string $content - file content
   * @throws Exception
   * @return string file path
  */
  protected function _createControllerFile($dir , $content)
  {
      if(file_exists($dir)){
          if(!is_dir($dir))
              throw new Exception('Invalid controller dir');
      }else{
          if(!@mkdir($dir , 0777 , true))
              throw new Exception(Lang::lang()->get('CANT_WRITE_FS') . ' ' . $dir);
      }

      if(!@file_put_contents($dir . '/' . 'Controller.php' ,  $content))
          throw new Exception('Cant create Controller');

      @chmod($dir . '/' . 'Controller.php', 0775);

      return $dir . '/' . 'Controller.php';
  }


  public function createVcModule($object , $projectFile)
  {
      $lang = Lang::lang();

      //prepare class name
      $name = Utils_String::formatClassName($object);

      $jsName = str_replace('_','', $name);
      $runNamespace = 'app'.$jsName.'Application';
      $classNamespace = 'app'.$jsName.'Components';

      $objectConfig = Db_Object_Config::getInstance($object);
      $primaryKey = $objectConfig->getPrimaryKey();

      $objectFieldsConfig = $objectConfig->getFieldsConfig(false);
      $objectFields = array();
      $searchFields = array();
      $linkedObjects = array();

      /*
       * Skip text fields
      */
      foreach($objectFieldsConfig as $key => $item)
      {
          if($objectConfig->isObjectLink($key) || $objectConfig->isMultiLink($key)){
              $linkedObjects[] = $objectConfig->getLinkedObject($key);
          }

          if(in_array($item['db_type'] , Db_Object_Builder::$textTypes , true) || $objectConfig->isObjectLink($key) || $objectConfig->isMultiLink($key))
              continue;

          if(isset($item['hidden']) && $item['hidden'])
              continue;


          $objectFields[] = $key;
          if(isset($item['is_search']) && $item['is_search'])
              $searchFields[] = $key;
      }

      $dataFields = array();
      foreach($objectConfig->getFieldsConfig(true) as $key => $item)
      {
          if(in_array($item['db_type'] , Db_Object_Builder::$textTypes , true))
              continue;

          if(isset($item['hidden']) && $item['hidden'])
              continue;


          $dataFields[] = $key;
      }

      array_unshift($objectFields , $objectConfig->getPrimaryKey());

      $linksToShow = array_keys($objectConfig->getLinks(
          [
              Db_Object_Config::LINK_OBJECT,
              Db_Object_Config::LINK_OBJECT_LIST,
             // Db_Object_Config::LINK_DICTIONARY  (dictionary renderer by default)
          ],
          false
      ));

      foreach($linksToShow as $k=>$v){
          if($objectConfig->isSystem($v)){
              unset($linksToShow[$v]);
          }
      }

      $controllerContent = '<?php ' . "\n" . 'class Backend_' . $name . '_Controller extends Backend_Controller_Crud_Vc{' . "\n" .
      '	 protected $_listFields = ["' . implode('","' , $dataFields) . '"];' . "\n" ;

      if(!empty($linksToShow)) {
          $controllerContent .= '	 protected $_listLinks = ["' . implode('","', $linksToShow) . '"];' . "\n";
      }else{
          $controllerContent.= '	 protected $_listLinks = [];' . "\n" ;
      }

      $controllerContent.=
      '  protected $_canViewObjects = ["' . implode('","' , $linkedObjects) . '"];' . "\n" .
      '}';

      /*
       * Create controller
      */
      $controllerDir = $this->appConfig->get('local_controllers') . $this->appConfig->get('backend_controllers_dir') . '/' . str_replace('_' , '/' , $name);
      $controllerFile = $this->_createControllerFile($controllerDir , $controllerContent);

      /*
       * Designer project
      */
      $project = new Designer_Project();
      $project->namespace = $classNamespace;
      $project->runnamespace = $runNamespace;

      /*
       * Project events
      */
      $eventManager = $project->getEventManager();

      /*
       * Data Storage
      */
      $storeFields = array(
          Ext_Factory::object('Data_Field',array('name' => 'published','type' => 'boolean')),
          Ext_Factory::object('Data_Field',array('name' => 'date_created','type' => 'date','dateFormat' =>'Y-m-d H:i:s')),
          Ext_Factory::object('Data_Field',array('name' => 'date_updated','type' => 'date','dateFormat' =>'Y-m-d H:i:s')),
          Ext_Factory::object('Data_Field',array('name' => 'date_published','type' => 'date','dateFormat' =>'Y-m-d H:i:s')),
          Ext_Factory::object('Data_Field',array('name' => 'last_version','type' => 'integer')),
          Ext_Factory::object('Data_Field',array('name' => 'published_version','type' => 'integer')),
          Ext_Factory::object('Data_Field',array('name' => 'user','type' => 'string')),
          Ext_Factory::object('Data_Field',array('name' => 'updater','type' => 'string')),
      );

      $storeFields = array_merge($storeFields , Backend_Designer_Import::checkImportORMFields($object ,  $dataFields));

      $urlTemplates =  $this->designerConfig->get('templates');


      $controllerUrl = Request::url(array($urlTemplates['adminpath'] , $object , '') , false);
      $storeUrl = Request::url(array($urlTemplates['adminpath'] ,  $object , 'list'));

      $modelName = $name.'Model';
      $model = Ext_Factory::object('Model');
      $model->setName($modelName);
      $model->idProperty = $primaryKey;
      $model->addFields($storeFields);

      $project->addObject(Designer_Project::COMPONENT_ROOT , $model, -10);

      $dataStore = Ext_Factory::object('Data_Store');
      $dataStore->setName('dataStore');
      $dataStore->model = $modelName;
      $dataStore->autoLoad = true;

      $dataProxy = Ext_Factory::object('Data_Proxy_Ajax');
      $dataProxy->type = 'ajax';

      $dataReader = Ext_Factory::object('Data_Reader_Json');
      $dataReader->rootProperty = 'data';
      $dataReader->totalProperty = 'count';
      $dataReader->type = 'json';

      $dataProxy->reader = $dataReader;
      $dataProxy->url = $storeUrl;

      $dataProxy->startParam = 'pager[start]';
      $dataProxy->limitParam = 'pager[limit]';
      $dataProxy->sortParam = 'pager[sort]';
      $dataProxy->directionParam = 'pager[dir]';
      $dataProxy->simpleSortMode = true;

      $dataStore->proxy = $dataProxy;
      $dataStore->remoteSort = true;

      $project->addObject(Designer_Project::LAYOUT_ROOT  , $dataStore);

      /*
       * Data grid
      */
      $dataGrid = Ext_Factory::object('Grid');
      $dataGrid->setName('dataGrid');
      $dataGrid->store = 'dataStore';
      $dataGrid->columnLines = true;
      $dataGrid->minHeight = 400;
      $dataGrid->title = $objectConfig->getTitle() . ' :: ' . $lang->HOME;
      $dataGrid->setAdvancedProperty('paging' , true);
      $dataGrid->viewConfig = '{enableTextSelection: true}';
      $dataGrid->extendedComponent(true);

      $this->addGridMethods($project , $dataGrid , $object , true);

      $eventManager->setEvent('dataGrid', 'itemdblclick', 'this.showEditWindow(record.get("id"));');

      $objectFieldList = Backend_Designer_Import::checkImportORMFields($object , $objectFields);


      $publishedRec = new stdClass();
      $publishedRec->name = 'published';
      $publishedRec->type = 'string';
      array_unshift($objectFieldList , $publishedRec);

      $createdRec = new stdClass();
      $createdRec->name =  'date_created';
      $createdRec->type='';
      $objectFieldList[] = $createdRec;

      $updatedRec = new stdClass();
      $updatedRec->name =  'date_updated';
      $updatedRec->type='';
      $objectFieldList[] = $updatedRec;

      foreach($objectFieldList as $fieldConfig)
      {

          switch($fieldConfig->type){
            case 'boolean':
                $column = Ext_Factory::object('Grid_Column_Boolean');
                break;
            case 'integer':
                $column = Ext_Factory::object('Grid_Column');
                break;
            case 'float':
                $column = Ext_Factory::object('Grid_Column_Number');
                if(isset($objectFieldsConfig[$fieldConfig->name]['db_precision']))
                    $column->format = '0,000.'.str_repeat('0' , $objectFieldsConfig[$fieldConfig->name]['db_precision']);
                break;
            case 'date':
                $column = Ext_Factory::object('Grid_Column_Date');
                if($objectFieldsConfig[$fieldConfig->name]['db_type'] == 'time')
                    $column->format = 'H:i:s';
                break;
            default:
                $column = Ext_Factory::object('Grid_Column');
          }

          if($objectConfig->fieldExists($fieldConfig->name)){
              $cfg = $objectConfig->getFieldConfig($fieldConfig->name);
              $column->text = $cfg['title'];
          }else{
              $column->text = $fieldConfig->name;
          }

          $column->dataIndex = $fieldConfig->name;
          $column->setName($fieldConfig->name);
          $column->itemId = $column->getName();

          switch($fieldConfig->name)
          {
                case $primaryKey:
                    $column->renderer = 'Ext_Component_Renderer_System_Version';
                    $column->text = '[js:] appLang.VERSIONS_HEADER';
                    $column->align = 'center';
                    $column->width = 147;
                    break;

                case 'published':
                    $column->renderer = 'Ext_Component_Renderer_System_Publish';
                    $column->text = '[js:] appLang.STATUS';
                    $column->align = 'center';
                    $column->width = 50;
                    break;

                case 'date_created':
                    $column->renderer = 'Ext_Component_Renderer_System_Creator';
                    $column->text = '[js:] appLang.CREATED_BY';
                    $column->align = 'center';
                    $column->width = 142;
                    break;

                case 'date_updated':
                    $column->renderer = 'Ext_Component_Renderer_System_Updater';
                    $column->text = '[js:] appLang.UPDATED_BY';
                    $column->align = 'center';
                    $column->width = 146;
                    break;
          }

          if($objectConfig->isDictionaryLink($fieldConfig->name)){
              $dictionary = $objectConfig->getLinkedDictionary($fieldConfig->name);
              $rendererHelper = new Ext_Helper_Grid_Column_Renderer();
              $rendererHelper->setType(Ext_Helper_Grid_Column_Renderer::TYPE_DICTIONARY);
              $rendererHelper->setValue($dictionary);
              $column->renderer = $rendererHelper;
          }

          $dataGrid->addColumn($column->getName() , $column , $parent = 0);
      }

      $column = Ext_Factory::object('Grid_Column_Action');
      $column->text = '[js:] appLang.ACTIONS';
      $column->setName($dataGrid->getName().'_actions');
      $column->align = 'center';
      $column->width = 50;

      $deleteButton = Ext_Factory::object('Grid_Column_Action_Button');
      $deleteButton->setName($dataGrid->getName().'_actions_delete');
      $deleteButton->text = 'dg_action_delete';
      $deleteButton->icon = '[%wroot%]i/system/delete.png';
      $deleteButton->tooltip = '[js:] appLang.DELETE';
      $deleteButton->isDisabled = 'function(){return !this.canDelete;}';

      $eventManager->setEvent($deleteButton->getName(), 'handler', 'this.deleteRecord(grid.getStore().getAt(rowIndex));');

      $column->addAction($deleteButton->getName() ,$deleteButton);
      $dataGrid->addColumn($column->getName() , $column , $parent = 0);

      $project->addObject(Designer_Project::COMPONENT_ROOT, $dataGrid);

      /**
       * Instance of data grid to layout
       */
      $gridInstance = Ext_Factory::object('Object_Instance');
      $gridInstance->setObject($dataGrid);
      $gridInstance->setName($dataGrid->getName());
      $project->getTree()->addItem($gridInstance->getName() . '_instance', Designer_Project::LAYOUT_ROOT, $gridInstance);

      /*
       * Top toolbar
       */
      $dockObject = Ext_Factory::object('Docked');
      $dockObject->setName($dataGrid->getName() . '__docked');
      $project->addObject($dataGrid->getName() , $dockObject);

      $filters = Ext_Factory::object('Toolbar');
      $filters->setName('filters');
      $project->addObject($dockObject->getName() , $filters);

      /*
       * Top toolbar items
       */
      $addButton = Ext_Factory::object('Button');
      $addButton->setName('addButton');
      $addButton->text = $lang->ADD_ITEM;
      $addButton->icon = '[%wroot%]i/system/add_icon.png';
      $eventManager->setEvent('addButton', 'click', 'this.showEditWindow(false);');

      $project->addObject($filters->getName() , $addButton);

      $sep1 = Ext_Factory::object('Toolbar_Separator');
      $sep1->setName('sep1');
      $project->addObject($filters->getName() , $sep1);

      if(!empty($searchFields)){
          $searchField = Ext_Factory::object('Component_Field_System_Searchfield');
          $searchField->setName('searchField');
          $searchField->width = 200;
          // $searchField->local = false;
          $searchField->store = $dataStore->getName();
          $searchField->fieldNames = json_encode($searchFields);

          $fill = Ext_Factory::object('Toolbar_Fill');
          $fill->setName('fill1');
          $project->addObject($filters->getName() , $fill);
          $project->addObject($filters->getName() , $searchField);
      }

      /*
       * Editor window
      */
      $editWindow = Ext_Factory::object('Component_Window_System_Crud_Vc');
      $editWindow->setName('editWindow');
      $editWindow->objectName = $object;
      $editWindow->controllerUrl = $controllerUrl;
      $editWindow->width = 800;
      $editWindow->height = 650;
      $editWindow->modal = true;
      $editWindow->resizable = true;
      $editWindow->extendedComponent(true);


      /*
       * Hide history panel
      */
      /*if(!$objectConfig->hasHistory())
       $editWindow->hideEastPanel = true;
      */

      $project->addObject(Designer_Project::COMPONENT_ROOT, $editWindow);

      $tab = Ext_Factory::object('Panel');
      $tab->setName($editWindow->getName() . '_generalTab');
      $tab->frame = false;
      $tab->border = false;
      $tab->layout = 'anchor';
      $tab->bodyPadding = 3;
      $tab->bodyCls = 'formBody';
      $tab->anchor = '100%';
      $tab->title = $lang->GENERAL;
      $tab->scrollable = true;
      $tab->fieldDefaults = "{labelAlign:'right', labelWidth: 160, anchor: '100%'}";

      $project->addObject($editWindow->getName() , $tab);

      $objectFieldList = array_keys($objectConfig->getFieldsConfig(false));

      foreach($objectFieldList as $field)
      {
          if($field == $primaryKey)
              continue;

          $fieldConfig = $objectConfig->getFieldConfig($field);

          if(isset($fieldConfig['hidden']) && $fieldConfig['hidden'])
              continue;

          $newField = Backend_Designer_Import::convertOrmFieldToExtField($field , $fieldConfig);

          if($newField === false)
              continue;

          $newField->setName($editWindow->getName() . '_' . $field);
          $fieldClass = $newField->getClass();

          if($fieldClass == 'Component_Field_System_Objectslist' || $fieldClass == 'Component_Field_System_Objectlink')
              $newField->controllerUrl = $controllerUrl;

          if(in_array($fieldClass , $this->tabTypes , true))
              $project->addObject($editWindow->getName() , $newField);
          else
              $project->addObject($tab->getName() , $newField);
      }

     /*
      * Create ActionJS code
      */
      $project->setActionJs($this->_createActionJS($object, $runNamespace, $classNamespace , true));

      /*
       * Save designer project
       */
      $designerStorage = Designer_Factory::getStorage($this->designerConfig);
      if(!$designerStorage->save($projectFile , $project , $this->designerConfig->get('vcs_support'))){
          @unlink($controllerFile);
          throw new Exception('Can`t create Designer project');
      }
      return true;
  }

  public function createModule($object , $projectFile)
  {
      $lang = Lang::lang();

      //prepare class name
      $name = Utils_String::formatClassName($object);

      $jsName = str_replace('_','', $name);
      $runNamespace = 'app'.$jsName.'Application';
      $classNamespace = 'app'.$jsName.'Components';

      $objectConfig = Db_Object_Config::getInstance($object);
      $primaryKey = $objectConfig->getPrimaryKey();

      $objectFieldsConfig = $objectConfig->getFieldsConfig(false);

      $objectFields = array();
      $searchFields = array();
      $linkedObjects = array();

      /*
       * Skip text fields
      */
      foreach($objectFieldsConfig as $key => $item)
      {
          if($objectConfig->isObjectLink($key) || $objectConfig->isMultiLink($key)){
              $linkedObjects[] = $objectConfig->getLinkedObject($key);
          }

          if(in_array($item['db_type'] , Db_Object_Builder::$textTypes , true) || $objectConfig->isObjectLink($key) || $objectConfig->isMultiLink($key))
              continue;

          if(isset($item['hidden']) && $item['hidden'])
              continue;

          $objectFields[] = $key;
          if(isset($item['is_search']) && $item['is_search'])
              $searchFields[] = $key;
      }

      $dataFields = array();
      foreach($objectConfig->getFieldsConfig(true) as $key => $item)
      {
          if(in_array($item['db_type'] , Db_Object_Builder::$textTypes , true))
              continue;

          if(isset($item['hidden']) && $item['hidden'])
              continue;

          $dataFields[] = $key;
      }

      array_unshift($objectFields , $primaryKey);

      $linksToShow = array_keys($objectConfig->getLinks(
          [
              Db_Object_Config::LINK_OBJECT,
              Db_Object_Config::LINK_OBJECT_LIST,
             // Db_Object_Config::LINK_DICTIONARY  (dictionary renderer by default)
          ],
          false
      ));

      foreach($linksToShow as $k=>$v){
          if($objectConfig->isSystem($v)){
              unset($linksToShow[$v]);
          }
      }

      $controllerContent = '<?php ' . "\n" . 'class Backend_' . $name . '_Controller extends Backend_Controller_Crud{' . "\n" .
      ' protected $_listFields = ["' . implode('","' , $dataFields) . '"];' . "\n";


      if(!empty($linksToShow)){
          $controllerContent.= ' protected $_listLinks = ["' . implode('","' , $linksToShow) . '"];' . "\n";
      }else{
          $controllerContent.= ' protected $_listLinks = [];' . "\n";
      }

      $controllerContent.=
      ' protected $_canViewObjects = ["' . implode('","' , $linkedObjects) . '"];' . "\n" .
      '} ';

      /*
       * Create controller
      */
      $controllerDir =  $this->appConfig->get('local_controllers') . $this->appConfig->get('backend_controllers_dir') . '/' . str_replace('_' , '/' , $name);
      $this->_createControllerFile($controllerDir , $controllerContent);
      @chmod( $controllerDir . DIRECTORY_SEPARATOR . 'Controller.php' , $controllerContent, 0775);


      /*
       * Designer project
      */
      $project = new Designer_Project();
      $project->namespace = $classNamespace;
      $project->runnamespace = $runNamespace;

      /*
       * Project events
      */
      $eventManager = $project->getEventManager();


      $storeFields = Backend_Designer_Import::checkImportORMFields($object , $dataFields);

      $urlTemplates =  $this->designerConfig->get('templates');


      $controllerUrl = Request::url(array($urlTemplates['adminpath'] ,  $object , ''),false);
      $storeUrl = Request::url(array($urlTemplates['adminpath'] , $object , 'list'));

      $modelName = $name.'Model';
      $model = Ext_Factory::object('Model');
      $model->setName($modelName);
      $model->idProperty = $primaryKey;
      $model->addFields($storeFields);

      $project->addObject(Designer_Project::COMPONENT_ROOT , $model, -10);


      $dataStore = Ext_Factory::object('Data_Store');
      $dataStore->setName('dataStore');
      $dataStore->autoLoad = true;
      $dataStore->model = $modelName;
     // $dataStore->addFields($storeFields);

      $dataProxy = Ext_Factory::object('Data_Proxy_Ajax');
      $dataProxy->type = 'ajax';

      $dataReader = Ext_Factory::object('Data_Reader_Json');
      $dataReader->rootProperty = 'data';
      $dataReader->totalProperty = 'count';
      $dataReader->type = 'json';

      $dataProxy->reader = $dataReader;
      $dataProxy->url = $storeUrl;

      $dataProxy->startParam = 'pager[start]';
      $dataProxy->limitParam = 'pager[limit]';
      $dataProxy->sortParam = 'pager[sort]';
      $dataProxy->directionParam = 'pager[dir]';
      $dataProxy->simpleSortMode = true;

      $dataStore->proxy = $dataProxy;
      $dataStore->remoteSort = true;

      $project->addObject(Designer_Project::LAYOUT_ROOT , $dataStore);

      /*
       * Data grid
      */
      $dataGrid = Ext_Factory::object('Grid');
      $dataGrid->setName('dataGrid');
      $dataGrid->store = 'dataStore';
      $dataGrid->columnLines = true;
      $dataGrid->title = $objectConfig->getTitle() . ' :: ' . $lang->HOME;
      $dataGrid->setAdvancedProperty('paging' , true);
      $dataGrid->viewConfig = '{enableTextSelection: true}';
      $dataGrid->minHeight = 400;
      $dataGrid->extendedComponent(true);

      $this->addGridMethods($project , $dataGrid , $object , false);

      $eventManager->setEvent('dataGrid', 'itemdblclick', 'this.showEditWindow(record.get("id"));');

      $objectFieldList = Backend_Designer_Import::checkImportORMFields($object , $objectFields);

      if(!empty($objectFieldList))
      {
          foreach($objectFieldList as $fieldConfig)
          {

              switch($fieldConfig->type){
                case 'boolean':
                    $column = Ext_Factory::object('Grid_Column_Boolean');
                    $column->renderer = 'Ext_Component_Renderer_System_Checkbox';
                    $column->width = 50;
                    $column->align = 'center';
                    break;
                case 'integer':
                    $column = Ext_Factory::object('Grid_Column');
                    break;

                case 'float':
                    $column = Ext_Factory::object('Grid_Column_Number');
                    if(isset($objectFieldsConfig[$fieldConfig->name]['db_precision']))
                        $column->format = '0,000.'.str_repeat('0' , $objectFieldsConfig[$fieldConfig->name]['db_precision']);
                    break;
                case 'date':
                    $column = Ext_Factory::object('Grid_Column_Date');
                    if($objectFieldsConfig[$fieldConfig->name]['db_type'] == 'time')
                        $column->format = 'H:i:s';
                    break;
                default:
                    $column = Ext_Factory::object('Grid_Column');
              }

              if($objectConfig->fieldExists($fieldConfig->name)){
                  $cfg = $objectConfig->getFieldConfig($fieldConfig->name);
                  $column->text = $cfg['title'];
              }else{
                  $column->text = $fieldConfig->name;
              }

              $column->dataIndex = $fieldConfig->name;
              $column->setName($fieldConfig->name);
              $column->itemId = $column->getName();

              if($objectConfig->isDictionaryLink($fieldConfig->name)){
                  $dictionary = $objectConfig->getLinkedDictionary($fieldConfig->name);
                  $rendererHelper = new Ext_Helper_Grid_Column_Renderer();
                  $rendererHelper->setType(Ext_Helper_Grid_Column_Renderer::TYPE_DICTIONARY);
                  $rendererHelper->setValue($dictionary);
                  $column->renderer = $rendererHelper;
              }

              $dataGrid->addColumn($column->getName() , $column , $parent = 0);
          }

          $column = Ext_Factory::object('Grid_Column_Action');
          $column->text = '[js:] appLang.ACTIONS';
          $column->setName($dataGrid->getName().'_actions');
          $column->align = 'center';
          $column->width = 50;

          $deleteButton = Ext_Factory::object('Grid_Column_Action_Button');
          $deleteButton->setName($dataGrid->getName().'_actions_delete');
          $deleteButton->text = 'dg_action_delete';
          $deleteButton->icon = '[%wroot%]i/system/delete.png';
          $deleteButton->tooltip = '[js:] appLang.DELETE';
          $deleteButton->isDisabled = 'function(){return !this.canDelete;}';

          $eventManager->setEvent($deleteButton->getName(), 'handler', 'this.deleteRecord(grid.getStore().getAt(rowIndex));');

          $column->addAction($deleteButton->getName() ,$deleteButton);
          $dataGrid->addColumn($column->getName() , $column , $parent = 0);
      }
      $project->addObject(Designer_Project::COMPONENT_ROOT , $dataGrid);


      /**
       * Instance of data grid to layout
       */
      $gridInstance = Ext_Factory::object('Object_Instance');
      $gridInstance->setObject($dataGrid);
      $gridInstance->setName($dataGrid->getName());
      $project->getTree()->addItem($gridInstance->getName() . '_instance', Designer_Project::LAYOUT_ROOT, $gridInstance);

      /*
       * Top toolbar
      */
      $dockObject = Ext_Factory::object('Docked');
      $dockObject->setName($dataGrid->getName() . '__docked');
      $project->addObject($dataGrid->getName() , $dockObject);

      $filters = Ext_Factory::object('Toolbar');
      $filters->setName('filters');
      $project->addObject($dockObject->getName() , $filters);

      /*
       * Top toolbar items
      */

      $addButton = Ext_Factory::object('Button');
      $addButton->setName('addButton');
      $addButton->text = $lang->ADD_ITEM;
      $addButton->icon = '[%wroot%]i/system/add_icon.png';

      $eventManager->setEvent('addButton', 'click', 'this.showEditWindow(false);');

      $project->addObject($filters->getName() , $addButton);

      $sep1 = Ext_Factory::object('Toolbar_Separator');
      $sep1->setName('sep1');
      $project->addObject($filters->getName() , $sep1);

      if(!empty($searchFields)){
          $searchField = Ext_Factory::object('Component_Field_System_Searchfield');
          $searchField->setName('searchField');
          $searchField->width = 200;
          $searchField->store = $dataStore->getName();
          $searchField->fieldNames = json_encode($searchFields);

          $fill = Ext_Factory::object('Toolbar_Fill');
          $fill->setName('fill1');
          $project->addObject($filters->getName() , $fill);
          $project->addObject($filters->getName() , $searchField);
      }

      /*
       * Editor window
      */
      $editWindow = Ext_Factory::object('Component_Window_System_Crud');
      $editWindow->setName('editWindow');
      $editWindow->objectName = $object;
      $editWindow->controllerUrl = $controllerUrl;
      $editWindow->width = 800;
      $editWindow->height = 650;
      $editWindow->modal = true;
      $editWindow->resizable = true;
      $editWindow->extendedComponent(true);
      $editWindow->showToolbar = false;


      if(!$objectConfig->hasHistory())
          $editWindow->hideEastPanel = true;

      $project->addObject(Designer_Project::COMPONENT_ROOT , $editWindow);

      $tab = Ext_Factory::object('Panel');
      $tab->setName($editWindow->getName() . '_generalTab');
      $tab->frame = false;
      $tab->border = false;
      $tab->layout = 'anchor';
      $tab->bodyPadding = 3;
      $tab->bodyCls = 'formBody';
      $tab->anchor = '100%';
      $tab->title = $lang->GENERAL;
      $tab->scrollable = true;
      $tab->fieldDefaults = "{
                    labelAlign: 'right',
                    labelWidth: 160,
                    anchor: '100%'
             }";

      $project->addObject($editWindow->getName() , $tab);

      $objectFieldList = array_keys($objectConfig->getFieldsConfig(false));

      foreach($objectFieldList as $field)
      {
          if($field == $primaryKey)
              continue;

          $fieldConfig = $objectConfig->getFieldConfig($field);

          if(isset($fieldConfig['hidden']) && $fieldConfig['hidden'])
              continue;

          $newField = Backend_Designer_Import::convertOrmFieldToExtField($field , $fieldConfig);

          if($newField === false)
              continue;

          $newField->setName($editWindow->getName() . '_' . $field);
          $fieldClass = $newField->getClass();

          if($fieldClass == 'Component_Field_System_Objectslist' || $fieldClass == 'Component_Field_System_Objectlink')
              $newField->controllerUrl = $controllerUrl;


          if(in_array($fieldClass , $this->tabTypes , true))
              $project->addObject($editWindow->getName() , $newField);
          else
              $project->addObject($tab->getName() , $newField);

      }

      /*
       * Save designer project
      */
      $designerStorage = Designer_Factory::getStorage( $this->designerConfig);

      /*
       * Create ActionJS code
       */
       $project->setActionJs($this->_createActionJS($object, $runNamespace, $classNamespace));

      if(!$designerStorage->save($projectFile , $project , $this->designerConfig->get('vcs_support')))
          throw new Exception('Can`t create Designer project');

      return true;
  }

  /**
   * Create actionJs code for designer project
   * @param string $object
   * @param string $runNamespace
   * @param string $classNamespace
   * @param boolean $vc - use version control
   * @return string
   * @throws Exception
   */
  protected function _createActionJS($object, $runNamespace , $classNamespace , $vc = false)
  {
      $actionJs = '
        /*
         * Here you can define application logic
         * To obtain info about current user access rights
         * you can use global scope JS vars canEdit , canDelete , canPublish
         * To access project elements, please use the namespace you defined in the config
         * For example: ' . $runNamespace . '.Panel or Ext.create("' . $classNamespace . '.editWindow", {});
         */
         Ext.onReady(function(){
                // Init permissions
                app.application.on("projectLoaded",function(module){
                    if(Ext.isEmpty(module) || module === "' . $object . '"){
                        if(!Ext.isEmpty(' . $runNamespace . '.dataGrid)){
                          ' . $runNamespace . '.dataGrid.setCanEdit(app.permissions.canEdit("' . $object . '"));
                          ' . $runNamespace . '.dataGrid.setCanDelete(app.permissions.canDelete("' . $object . '"));';

                        if ($vc) {
                            $actionJs .= '
                            ' . $runNamespace . '.dataGrid.setCanPublish(app.permissions.canPublish("' . $object . '"));';
                        }
                        $actionJs.= '
                           ' . $runNamespace.'.dataGrid.getView().refresh();
                        }
                    }
                });
          });';

    return $actionJs;
  }

  public function addGridMethods(Designer_Project $project ,  Ext_Object $grid , $object, $vc = false)
  {
      $methodsManager =  $project->getMethodManager();

      $m = $methodsManager->addMethod($grid->getName() , 'initComponent' , array() ,
        '
            this.addDesignerItems();
            this.callParent();

            if(!Ext.isEmpty(this.canEdit) && !Ext.isEmpty(this.setCanEdit)){
                this.setCanEdit(this.canEdit);
            }else{
                this.canEdit = false;
            }

            if(!Ext.isEmpty(this.canDelete) && !Ext.isEmpty(this.setCanDelete)){
                this.setCanDelete(this.canDelete);
            }else{
                this.canDelete = false;
            }

            if(!Ext.isEmpty(this.canPublish) && !Ext.isEmpty(this.setCanPublish)){
                this.setCanPublish(this.canPublish);
            }else{
                this.canPublish = false;
            }
        '
      );

      $urlTemplates =  $this->designerConfig->get('templates');
      $deleteUrl = Request::url(array($urlTemplates['adminpath'] ,  $object , 'delete'));

      $m = $methodsManager->addMethod($grid->getName() , 'deleteRecord' , array(array('name'=>'record','type'=>'Ext.data.record')) ,
          '
            Ext.Ajax.request({
                url:"'.$deleteUrl.'",
                method: "post",
                scope:this,
                params:{
                    id: record.get("id")
                },
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(response.success){
                        this.getStore().remove(record);
                    }else{
                        Ext.Msg.alert(appLang.MESSAGE , response.msg);
                    }
                },
                failure:function(){
                    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                }
            });
          '
     );
    $m->setDescription('Delete record');

    $m = $methodsManager->addMethod($grid->getName(), 'setCanEdit' , array(array('name'=>'canEdit','type'=>'boolean')) , '
        this.canEdit = canEdit;
        if(canEdit){
          this.childObjects.addButton.show();
        }else{
          this.childObjects.addButton.hide();
        }
        this.getView().refresh();
    ');
     $m->setDescription('Set edit permission');

    $m = $methodsManager->addMethod($grid->getName(), 'setCanDelete' , array(array('name'=>'canDelete','type'=>'boolean')) , ' this.canDelete = canDelete;');
    $m->setDescription('Set delete permission');

    if($vc){
      $m = $methodsManager->addMethod($grid->getName(), 'setCanPublish' , array(array('name'=>'canPublish','type'=>'boolean')) , 'this.canPublish = canPublish;');
      $m->setDescription('Set publish permission');
    }
    $editCode ='
        var win = Ext.create("'.$project->namespace.'.editWindow", {
                  dataItemId:id,
                  canDelete:this.canDelete,';
      if($vc)
          $editCode.='
                  canPublish:this.canPublish,';

      $editCode .= 'canEdit:this.canEdit';

      $editCode.='
            });

            win.on("dataSaved",function(){
                this.getStore().load();
              ';
      if(!$vc){
          $editCode.='win.close();';
      }

      $editCode.='},this);

            win.show();
    ';


    $m = $methodsManager->addMethod($grid->getName(), 'showEditWindow' ,  array(array('name'=>'id','type'=>'integer')) , $editCode);
    $m->setDescription('Show editor window');
  }
}
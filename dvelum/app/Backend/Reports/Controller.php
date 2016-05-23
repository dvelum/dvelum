<?php
/**
 * @todo cleanup the code (Оптимизировать повторяющиеся конструкции)
 * @author Kirill
 */
class Backend_Reports_Controller extends Backend_Controller
{
    /**
     * @var Db_Query_Storage_Adapter_Abstract
     */
    protected $_storage;
    /**
     * @var Store_Session
     */
    protected $_session;

    protected $_reportsPath;

    public function __construct()
    {
        /*
         * Load classes by autoloader
         * class should be loaded before unserializing
         */
        class_exists('Db_Query');
        class_exists('Db_Query_Part');

        parent::__construct();

        if($this->_configMain->get('use_cache') && $this->_cache)
            Db_Query_Storage::setCache($this->_cache);

        $this->_storage = Db_Query_Storage::getInstance('file');
        $this->_session = Store_Session::getInstance('Reports');

        $conManager = Model::getDefaultDbManager();
        $this->_db = $conManager->getDbConnection('default');
        $this->_reportsPath = Config::storage()->getWrite() .  $this->_configMain->get('report_configs');
    }


    public function indexAction()
    {
        $res = Resource::getInstance();
        $this->_resource->addInlineJs('
            var canEdit = '.((boolean)$this->_user->canEdit($this->_module)).';
            var canDelete = '.((boolean)$this->_user->canDelete($this->_module)).';
        ');

        $this->_resource->addCss('/js/lib/CodeMirror/lib/codemirror.css');
        $this->_resource->addCss('/js/lib/CodeMirror/addon/dialog/dialog.css');
        $this->_resource->addCss('/js/lib/CodeMirror/addon/hint/show-hint.css');
        $this->_resource->addCss('/js/lib/CodeMirror/theme/eclipse.css');

        $res->addJs('/js/lib/CodeMirror/lib/codemirror.js', 0);
        $codeMirrorFiles = [
            '/js/lib/CodeMirror/addon/hint/show-hint.js',
            '/js/lib/CodeMirror/addon/hint/javascript-hint.js',
            '/js/lib/CodeMirror/addon/dialog/dialog.js',
            '/js/lib/CodeMirror/addon/search/search.js',
            '/js/lib/CodeMirror/addon/search/searchcursor.js',
            '/js/lib/CodeMirror/addon/search/match-highlighter.js',
            '/js/lib/CodeMirror/addon/selection/active-line.js',
            '/js/lib/CodeMirror/mode/sql/sql.js',
        ];

        foreach($codeMirrorFiles as $file){
            $res->addJs($file, 1);
        }

        $res->addJs('/js/app/system/FilesystemWindow.js', 1);
        $res->addJs('/js/app/system/SqlEditor.js', 1);
        $res->addJs('/js/app/system/report/filter.js', 1);
        $res->addJs('/js/app/system/report/config.js', 1);
        $res->addJs('/js/app/system/report/results.js', 1);
        $res->addJs('/js/app/system/report/record.js', 1);
        $res->addJs('/js/app/system/Reports.js', 1);
        $res->addJs('/js/app/system/crud/reports.js', 2);
    }
    /**
     * Check if report is loaded
     */
    public function checkloadedAction()
    {

        if($this->_session->keyExists('loaded') && $this->_session->get('loaded'))
        {
            $query = $this->_session->get('query');
            $rootPart = $query->getRootPart();
            $items = array();
            $partconfig = array();
            if($rootPart){
                $helper = Db_Object_Config::getInstance($rootPart->getObject());
                $partconfig['title'] = $helper->get('title');
                $partconfig['object'] = $rootPart->getObject();
                $items = $this->_partConfig($query , $rootPart);
            }
            Response::jsonSuccess(array('items'=>$items,'partconfig'=>$partconfig));
        }
        else
        {
            Response::jsonError();
        }
    }

    /**
     * Load query into the session storage and return itemsconfig
     */
    public function loadAction()
    {
        $file = Request::post('file', 'string', false);
        $path = $this->_reportsPath;


        if(!$file || !file_exists($path.$file))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $queryId = $path.$file;
        $query = $this->_storage->load($queryId);

        $this->_session->set('loaded' , true);
        $this->_session->set('query' , $query);
        $this->_session->set('file' , $queryId);

        Response::jsonSuccess();
    }

    /**
     * Clear report
     */
    public function clearAction()
    {
        $this->_checkCanEdit();
        $this->_checkLoaded();

        $query = new Db_Query();
        $this->_session->set('query' , $query);

        Response::jsonSuccess();
    }

    /**
     * Load Query part fields config
     */
    public function loadpartAction()
    {
        $parentPart= Request::post('parentpart', 'string', '');
        $parentField= Request::post('parentfield', 'string', '');
        $object = Request::post('object', 'string', false);

        if(!$object)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' 1');

        try{
            $objectCfg = Db_Object_Config::getInstance($object);
        }catch (Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST .' 2');
        }

        $this->_checkLoaded();

        $query = $this->_session->get('query');
        $part = $query->findChild($parentPart, $parentField);

        if(!$part)
            Response::jsonError($this->_lang->WRONG_REQUEST.' 3');

        Response::jsonSuccess($this->_partConfig($query , $part));
    }

    /**
     * Remove part from report
     */
    public function deselectsubAction()
    {
        $this->_checkCanDelete();

        $parentPart= Request::post('parentpart', 'string', '');
        $parentField= Request::post('parentfield', 'string', '');
        $object = Request::post('object', 'string', false);

        if(!$object)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 1');

        $this->_checkLoaded();

        $query = $this->_session->get('query');

        $query->getPart($parentPart)->setFieldCfg($parentField,'selectSub',false);

        $child = $query->findChild($parentPart, $parentField);

        if($child && $query->removePart($child->getId()))
        {
            $this->_session->set('query' , $query);
            Response::jsonSuccess();

        }else{
            Response::jsonError($this->_lang->CANT_EXEC);
        }
    }

    /**
     * Set Join type for report part
     */
    public function setjoinAction()
    {
        $this->_checkCanEdit();

        $parentPart= Request::post('parentpart', 'string', '');
        $parentField= Request::post('parentfield', 'string', '');
        $childField = Request::post('childfield', 'string', '');
        $object = Request::post('object', 'string', false);
        $joinType = Request::post('jointype', 'integer', 1);

        if(!$object)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 1');

        $this->_checkLoaded();

        $query = $this->_session->get('query');
        /**
         * @var Db_Query_Part
         */
        $part = $query->getPart(Db_Query_Part::findId($parentPart, $parentField, $childField , $object));
        if(!$part)
            Response::jsonError($this->_lang->WRONG_REQUES);

        $part->setJoinType($joinType);
        $this->_session->set('query' , $query);
        Response::jsonSuccess();
    }

    /**
     * Save field config
     */
    public function savefieldAction()
    {
        $this->_checkCanEdit();

        $part = Request::post('part', 'string', false);
        $fieldOption = Request::post('fieldoption', 'string', '');
        $field = Request::post('field', 'string', '');
        $value = Request::post('value', 'string', '');

        $accepted = array('select','title','alias');

        if(!$part || !$field || !in_array($fieldOption, $accepted,true))
            Response::jsonError($this->_lang->WRONG_REQUEST .' 1');

        if($fieldOption === 'select')
            $value = Filter::filterValue('boolean', $value);


        $this->_checkLoaded();

        $query = $this->_session->get('query');
        $part = $query->getPart($part);

        if($part === false)
            Response::jsonError($this->_lang->WRONG_REQUEST.' 3');

        $part->setFieldCfg($field, $fieldOption, $value);
        $this->_session->set('query' , $query);

        Response::jsonSuccess();
    }

    /**
     * Save report
     */
    public function saveAction()
    {
        $this->_checkCanEdit();
        $this->_checkLoaded();

        if(!User::getInstance()->canEdit($this->_module))
            Response::jsonError($this->_lang->CANT_MODIFY);

        if($this->_storage->save($this->_session->get('file'), $this->_session->get('query')))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_WRITE_FS);
    }

    /**
     * Load query SQL
     */
    public function loadsqlAction()
    {
        $this->_checkLoaded();

        $query = $this->_session->get('query');

        Response::jsonSuccess(self::simpleFormatSql((string)$query->getSql()));
    }

    /**
     * Simple SQL format method, can return invalid formating in field values
     * @param string $query
     * @return string
     */
    static public function simpleFormatSql($query)
    {
        $newLines = array(' from ',' where ',' limit ',' order by ' ,' left ' ,' right ', ' inner ' , ' join ');
        $newLines2 = array("\nFROM\n\t ","\nWHERE\n\t ","LIMIT ","ORDER BY\n\t " ,"\nLEFT " ,"\nRIGHT ", "\nINNER ", " JOIN\n\t ");
        $str = str_ireplace($newLines, $newLines2, $query);
        return $str;
    }
    
    /**
     * Add Query Part
     */
    public function addpartAction()
    {
        $this->_checkCanEdit();

        $partId = Request::post('partid', 'string', false);
        $objectField = Request::post('objectfield', 'string', false);
        $subObject = Request::post('subobject', 'string', false);
        //$childField = Request::post('childfield', 'string', '');

        if(!$partId || !$objectField)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $this->_checkLoaded();

        $query = $this->_session->get('query');

        $part = $query->getPart($partId);

        if($part === false)
            Response::jsonError($this->_lang->WRONG_REQUEST .' code 3');

        $oCfg = Db_Object_Config::getInstance($part->getObject());

        $childPart = $query->findChild($partId , $objectField);
        if($childPart)
            $query->removePart($childPart->getId());

        $newPart = new Db_Query_Part();
        $newPart->setObject($subObject);
        $newPart->setParentField($objectField);
        $newPart->setChildField(Db_Object_Config::getInstance($subObject)->getPrimaryKey());

        if(!$query->addPart($newPart , $partId))
            Response::jsonArray(array('success'=>true,'msg'=>$this->_lang->REPORT_RECURSION_LIMIT,'limit'=>true));

        $part->setFieldCfg($objectField, 'selectSub', true);

        $this->_session->set('query' , $query);

        Response::jsonSuccess();
    }

    /**
     * Get object fields
     */
    public function objectfieldsAction()
    {
        $this->_checkLoaded();

        /**
         * @var Db_Query
         */
        $query = $this->_session->get('query');

        $object = Request::post('object', 'string', '0');

        if($object === '0'){
            $object = $query->getRootPart()->getObject();
        }

        try{
            $config = Db_Object_Config::getInstance($object);
        }catch(Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }

        $fields = $config->getFieldsConfig(true);
        $result = array();

        foreach ($fields as $name=>$cfg)
            $result[] = array('id'=>$name,'title'=>$cfg['title']);

        Response::jsonSuccess($result);
    }

    protected function _partConfig(Db_Query $query , Db_Query_Part $part)
    {
        $cfg = array();
        $fields = $part->getFields();
        $objectConfig = Db_Object_Config::getInstance($part->getObject());

        $mainPanel = new stdClass();
        $mainPanel->xtype='panel';
        $mainPanel->border=false;

        ksort($fields);

        foreach ($fields as $name=>$config)
        {
            $obj = new stdClass();
            $obj->xtype='reportfield';
            $obj->valueSelected=$config['select'];
            $obj->valueTitle = $config['title'];
            $obj->valueAlias = $config['alias'];
            $obj->valueField = $name;
            $obj->valueIsLink= false;
            $obj->valueSelectSub = $config['selectSub'];
            $obj->valueObject = $part->getObject();
            $obj->valuePartId = $part->getId();
            $obj->valueSubObject='';
            $obj->valueSubObjectTtile='';

            if($config['isLink'] && !$objectConfig->isDictionaryLink($name))
            {
                $obj->valueIsLink = true;

                $child = $query->findChild($part->getId(), $name);

                if($child !==false)
                    $linked = $child->getObject();
                else
                    $linked = $objectConfig->getLinkedObject($name);


                if($linked){
                    $obj->valueSubObject = $linked;
                    $obj->valueSubObjectTtile = Db_Object_Config::getInstance($linked)->get('title');
                }
            }

            $cfg[] = $obj;
        }

        $mainPanel->items = $cfg;

        return array(
            'items'=>$mainPanel,
            'objectcfg'=>array(
                'join'=>$part->joinType,
                'title'=>$objectConfig->get('title'),
                'object'=>$part->getObject(),
                'childField'=>$part->getChildField()
            )
        );
    }

    /**
     * Files list
     */
    public function fslistAction()
    {
        $path = Request::post('node', 'string', '');
        $path = str_replace('.','', $path);

        if(!is_dir($this->_reportsPath) && !@mkdir($this->_reportsPath , 0755)){
            Response::jsonError($this->_lang->get('CANT_WRITE_FS') . ' ' . $this->_reportsPath);
        }

        $files = File::scanFiles($this->_reportsPath . $path, array('.dat'),false,File::Files_Dirs);

        if(empty($files))
            Response::jsonArray(array());

        $list = array();

        foreach($files as $k=>$fpath)
        {
            $basename = basename($fpath);
            if($basename[0] === '.')
                continue;

            $obj = new stdClass();
            $obj->id = str_replace($this->_reportsPath, '', $fpath);
            $obj->text = basename($fpath);

            if(is_dir($fpath))
            {
                $obj->expanded = false;
                $obj->leaf = false;
            }
            else
            {
                $obj->leaf = true;
            }
            $list[] = $obj;
        }
        Response::jsonArray($list);
    }

    /**
     * Create config subfolder
     */
    public function fsmakedirAction()
    {
        $this->_checkCanEdit();

        $name = Request::post('name', 'string', '');
        $path = Request::post('path', 'string', '');

        $name = str_replace(array(DIRECTORY_SEPARATOR), '' , $name);

        if(!strlen($name))
            Response::jsonError($this->_lang->WRONG_REQUEST . ' [code 1]');

        $newPath = $this->_reportsPath;

        if(strlen($path))
        {
            if(!is_dir($newPath. $path))
                Response::jsonError($this->_lang->WRONG_REQUEST  . ' [code 2]');

            $newPath.= $path . DIRECTORY_SEPARATOR;
        }

        $newPath.= DIRECTORY_SEPARATOR . $name;

        if(mkdir($newPath, 0775))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_WRITE_FS);
    }

    /**
     * Create new report
     */
    public function fsmakefileAction()
    {
        $this->_checkCanEdit();

        $name = Request::post('name', 'string', '');
        $path = Request::post('path', 'string', '');

        $name = str_replace(array(DIRECTORY_SEPARATOR), '' , $name);

        if(!strlen($name))
            Response::jsonError($this->_lang->WRONG_REQUEST . ' [code 1]');

        $newPath = $this->_reportsPath;

        if(strlen($path))
            $filepath = $newPath. $path . DIRECTORY_SEPARATOR . $name.'.report.dat';
        else
            $filepath = $newPath . $name . '.report.dat';

        /**
         * @todo refactor fast fix
         */
        $filepath = str_replace('//','/', $filepath);

        if(file_exists($filepath))
            Response::jsonError($this->_lang->FILE_EXISTS);

        $obj = new Db_Query();
        $storage =  Db_Query_Storage::getInstance('file');
        if($storage->save($filepath, $obj))
            Response::jsonSuccess(array('file'=>DIRECTORY_SEPARATOR . str_replace($newPath, '', $filepath)));
        else
            Response::jsonError($this->_lang->CANT_WRITE_FS);
    }

    protected function _checkLoaded()
    {
        if(!$this->_session->keyExists('loaded') || !$this->_session->get('loaded'))
            Response::jsonError($this->_lang->MSG_REPORT_NOT_LOADED);
    }

    /**
     * Get results grid config
     */
    public function resultsAction()
    {
        $this->_checkLoaded();

        $query = $this->_session->get('query');
        $fieldsCfg = $query->getSelectedColumns();

        if(empty($fieldsCfg))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $fields = array();
        $columns = array();

        foreach($fieldsCfg as $item)
        {
            $column = new stdClass();
            $field = new stdClass();

            $field->name = $item['name'];
            $field->type = 'text';

            $column->dataIndex = $item['name'];
            $column->header = $item['title'];
            $column->flex = 1;

            $fields[] = $field;
            $columns[] = $column;
        }
        Response::jsonSuccess(array('fields'=>$fields,'columns'=>$columns));
    }

    /**
     * Get query results
     */
    public function dataAction()
    {
        $pager = Request::post('pager', 'array', array());

        $this->_checkLoaded();

        $query = $this->_session->get('query');

        /**
         * @var Db_Select
         */
        $sql = $query->getSql();

        if(!empty($pager))
            Model::queryAddPagerParams($sql, $pager);

        try{
          $data = $this->_db->fetchAll($sql);
          $count = $this->_db->fetchOne($query->getCountSql());
        }catch (Exception $e){
           Response::jsonError($e->getMessage());
        }

        Response::jsonSuccess($data,array('count'=>$count));
    }

    /**
     * Get Orm objects list
     */
    public function objectsAction()
    {
        $manager = new Db_Object_Manager();
        $list = $manager->getRegisteredObjects();
        foreach ($list as $key)
           $data[]= array('id'=>$key , 'title'=>Db_Object_Config::getInstance($key)->getTitle());

        Response::jsonArray($data);
    }

    /**
     * Add base object into report
     */
    public function addbaseAction()
    {
        $this->_checkCanEdit();
        $this->_checkLoaded();

        if(!User::getInstance()->canEdit($this->_module))
            Response::jsonError($this->_lang->CANT_MODIFY);

        $objectName = Request::post('object', 'string', false);

        /**
         * @var Db_Query
         */
        $query = $this->_session->get('query');

        $part = new Db_Query_Part();
        $part->setObject($objectName);
        $query->addPart($part);
        $this->_session->set('query',$query);
        Response::jsonSuccess();
    }

    /**
     * Add related object selection
     */
    public function addSubAction()
    {
        $this->_checkCanEdit();
        $this->_checkLoaded();

        $baseField = Request::post('basefield', 'string', false);
        $subObject = Request::post('subobject', 'string', false);
        $subField = Request::post('subfield', 'string', false);
        $join = Request::post('join', 'integer', 1);

        if(!$baseField || !$subObject || !$subField || !$join)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $query = $this->_session->get('query');

        $basePart = $query->getRootPart();

        $childPart = $query->findChild($basePart->getId(), $baseField);

        if($childPart!==false)
            $query->removePart($childPart->getId());

        $newPart = new Db_Query_Part();
        $newPart->setObject($subObject);
        $newPart->setParentField($baseField);
        $newPart->setChildField($subField);
        $newPart->setJoinType($join);

        if(!$query->addPart($newPart , $basePart->getId()))
            Response::jsonArray(array('success'=>false,'msg'=>$this->_lang->CANT_EXEC));

        $basePart->setFieldCfg($baseField, 'selectSub', true);
        $basePart->setFieldCfg($baseField, 'isLink', true);

        $this->_session->set('query' , $query);
        Response::jsonSuccess();
    }

    /**
     * Clear report session
     */
    public function closeAction()
    {
        $this->_session->remove('loaded');
        $this->_session->remove('query');
        $this->_session->remove('file');
        Response::jsonSuccess();
    }

    /**
     * Load CSV file
     */
    public function exportcsvAction()
    {
        $this->_checkLoaded();

        $query = $this->_session->get('query');
        /**
         * @var Db_Select
         */
        $sql = $query->getSql();
        $data = $this->_db->fetchAll($sql);

        $fieldsCfg = $query->getSelectedColumns();

        $titles = Utils::fetchCol('title', Utils::rekey('name', $fieldsCfg));

        $data = array_merge(array($titles) , $data);

        $csv =  Export::factory(Export::CSV, 'Export_Layout_Table_Csv', $data);
        $csv->setFileName('Report');
        $csv->stream();

    }

    /**
     * Get list of resultset filters
     */
    public function filterslistAction()
    {
        $this->_checkLoaded();
        $query = $this->_session->get('query');
        $result = array();
        Response::jsonSuccess($result);
    }

    /**
     * Get list of selected objects
     */
    public function objectlistAction()
    {
        $this->_checkLoaded();

        $query = $this->_session->get('query');
        $result = array();

        $objects = $query->getSelectedObjects();
        if(!empty($objects))
            foreach ($objects as $object)
                $result[] = array('id'=>$object , 'title'=>Db_Object_Config::getInstance($object)->get('title'));

        Response::jsonSuccess($result);
    }

    /**
     * Get object fields
     */
    public function fieldlistAction()
    {
        $object = Request::post('object', 'string', false);
        if(!$object)
            Response::jsonSuccess(array());

        $this->_checkLoaded();

        $query = $this->_session->get('query');
        $result = array();

        $fields = Db_Object_Config::getInstance($object)->getFieldsConfig(true);
        if(!empty($fields))
            foreach ($fields as $code =>$config)
                $result[] = array('id'=>$code,'title'=>$config['title']);

        Response::jsonSuccess($result);
    }

    /**
     * Remove Query condition
     */
    public function removeconditionAction()
    {
        $this->_checkCanEdit();
        $this->_checkLoaded();

        $query = $this->_session->get('query');
        $id = Request::post('id','integer',false);
        if($id === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $query->removeCondition($id);
        $this->_session->set('query' , $query);

        Response::jsonSuccess();
    }

    /**
     * Get list of SQL operators
     */
    public function operatorlistAction()
    {
        $dictionary = Dictionary::getInstance('sqloperator')->getData();
        $result = array();
        foreach ($dictionary as $key=>$value)
            $result[] = array('id'=>$key,'title'=>$value);

        Response::jsonSuccess($result);
    }

    /**
     * Get list of Query conditions
     */
    public function conditionslistAction()
    {
        $this->_checkLoaded();

        $query = $this->_session->get('query');

        $result = array();
        $conditions = $query->getConditions();

        if(!empty($conditions)){
            $dict = Dictionary::getInstance('sqloperator');
            foreach ($conditions as $id=>$object){
                $tmp = get_object_vars($object);
                if($dict->isValidKey($tmp['operator']))
                    $tmp['operator'] = $dict->getValue($tmp['operator']);
                $tmp['id'] = $id;
                $result[] = $tmp;
            }
        }
        Response::jsonSuccess($result);
    }

    /**
     * Save SQL condition
     * @todo check incoming values
     */
    public function saveconditionAction()
    {
        $this->_checkCanEdit();
        $this->_checkLoaded();

        $query = $this->_session->get('query');

        $id = Request::post('id','integer',-1);
        $object = Request::post('object','string',false);
        $field= Request::post('field','string',false);
        $operator= Request::post('operator','string',false);
        $value = Request::post('value','string',false);
        $value2= Request::post('value2','string',false);

        $dbCondition = new Db_Query_Condition();
        $dbCondition->object = $object;
        $dbCondition->field = $field;
        $dbCondition->operator = $operator;
        $dbCondition->value = $value;
        $dbCondition->value2 = $value2;

        if($id===-1)
            $query->addCondition($dbCondition);
        else
            $query->setCondition($id , $dbCondition);

        $this->_session->set('query' , $query);
        Response::jsonSuccess();
    }

    /**
     * Get Db_Query_Condition config
     */
    public function loadconditionAction()
    {
        $this->_checkLoaded();

        $query = $this->_session->get('query');

        $id = Request::post('id','integer',false);

        if($id===false)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 2');

        $condition = $query->getCondition($id);

        if($condition === false)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 3');

        $data = get_object_vars($condition);
        $data['id'] = $id;
        Response::jsonSuccess($data);
    }

    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] =  '/js/app/system/Reports.js';

        $projectData['includes']['css'][] = '/js/lib/CodeMirror/lib/codemirror.css';
        $projectData['includes']['css'][] = '/js/lib/CodeMirror/addon/dialog/dialog.css';
        $projectData['includes']['css'][] = '/js/lib/CodeMirror/addon/hint/show-hint.css';
        $projectData['includes']['css'][] = '/js/lib/CodeMirror/theme/eclipse.css';


        $projectData['includes']['js'][] = '/js/lib/CodeMirror/lib/codemirror.js';
        $projectData['includes']['js'][] = '/js/lib/CodeMirror/addon/hint/show-hint.js';
        $projectData['includes']['js'][] = '/js/lib/CodeMirror/addon/hint/javascript-hint.js';
        $projectData['includes']['js'][] = '/js/lib/CodeMirror/addon/dialog/dialog.js';
        $projectData['includes']['js'][] = '/js/lib/CodeMirror/addon/search/search.js';
        $projectData['includes']['js'][] = '/js/lib/CodeMirror/addon/search/searchcursor.js';
        $projectData['includes']['js'][] = '/js/lib/CodeMirror/addon/search/match-highlighter.js';
        $projectData['includes']['js'][] = '/js/lib/CodeMirror/addon/selection/active-line.js';
        $projectData['includes']['js'][] = '/js/lib/CodeMirror/mode/sql/sql.js';

        $projectData['includes']['js'][] = '/js/app/system/FilesystemWindow.js';
        $projectData['includes']['js'][] = '/js/app/system/SqlEditor.js';
        $projectData['includes']['js'][] = '/js/app/system/report/filter.js';
        $projectData['includes']['js'][] = '/js/app/system/report/config.js';
        $projectData['includes']['js'][] = '/js/app/system/report/results.js';;
        $projectData['includes']['js'][] = '/js/app/system/report/record.js';

        /*
         * Module bootstrap
         */
        if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}
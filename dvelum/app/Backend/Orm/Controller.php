<?php
/**
 * ORM UI Controller
 */
class Backend_Orm_Controller extends Backend_Controller
{
    const UML_MAP_CFG = 'umlMap.php';

    protected $encryptContainerPrefix = 'encrypt_';
    protected $decryptContainerPrefix = 'decrypt_';

    public function __construct()
    {
        parent::__construct();

        Db_Object_Builder::writeLog($this->_configMain['use_orm_build_log']);
        Db_Object_Builder::setLogPrefix($this->_configMain['development_version'].'_build_log.sql');
        Db_Object_Builder::setLogsPath($this->_configMain['orm_log_path']);
    }

    public function indexAction()
    {
        $version = Config::storage()->get('versions.php')->get('orm');

        $res = Resource::getInstance();
        $dbConfigs = array();

        foreach ($this->_configMain->get('db_configs') as $k=>$v){
            $dbConfigs[]= array('id'=>$k , 'title'=>$this->_lang->get($v['title']));
        }
        //tooltips
        $lPath = $this->_configMain->get('language').'/orm.php';
        Lang::addDictionaryLoader('orm_tooltips', $lPath, Config::File_Array);

        $this->_resource->addInlineJs('
          var canPublish =  '.((integer)$this->_user->canPublish($this->_module)).';
          var canEdit = '.((integer)$this->_user->canEdit($this->_module)).';
          var canDelete = '.((integer)$this->_user->canDelete($this->_module)).';
          var useForeignKeys = '.((integer)$this->_configMain['foreign_keys']).';
          var canUseBackup = false;
          var dbConfigsList = '.json_encode($dbConfigs).';
        ');

        $this->_resource->addRawJs('var ormTooltips = '.Lang::lang('orm_tooltips')->getJson().';');

        $res->addJs('/js/app/system/SearchPanel.js'  , 0);
        $res->addJs('/js/app/system/ORM.js?v='.$version , 2);

        $res->addJs('/js/app/system/EditWindow.js' , 1);
        $res->addJs('/js/app/system/HistoryPanel.js' , 1);
        $res->addJs('/js/app/system/ContentWindow.js' , 1);
        $res->addJs('/js/app/system/RevisionPanel.js', 2);
        $res->addJs('/js/app/system/RelatedGridPanel.js', 2);
        Model::factory('Medialib')->includeScripts();
        $res->addJs('/js/lib/uml/raphael.js'  , 3);
        // $res->addJs('/js/lib/uml/raphael.2.1.min.js'  , 3);
        $res->addJs('/js/lib/uml/joint.js'  , 4);
        $res->addJs('/js/lib/uml/joint.dia.js'  , 5);
        $res->addJs('/js/lib/uml/joint.dia.uml.js'  , 6);
        $res->addJs('/js/app/system/crud/orm.js', 7);
    }

    /**
     * Get database statistics
     * @return array
     * @throws Exception
     */
    static public function getDbStats()
    {
        $data = array();

        /*
         * Getting list of objects
         */
        $manager = new Db_Object_Manager();

        $names = $manager->getRegisteredObjects();
        if(empty($names))
            return array();

        $tables = array();

        /*
         * forming result set
         */
        foreach ($names as $objectName)
        {
            $configObject = Db_Object_Config::getInstance($objectName);
            $objectModel = Model::factory($objectName);
            $config =  $configObject->__toArray();
            $objectTable = $objectModel->table();
            $builder = new Db_Object_Builder($objectName);

            $records = 0;
            $dataLength = 0;
            $indexLength=0;
            $size = 0;

            $oModel = Model::factory($objectName);
            $oDb = $oModel->getDbConnection();
            $oDbConfig = $oDb->getConfig();
            $oDbHash = md5(serialize($oDbConfig));

            $canConnect = true;

            if(!isset($tables[$oDbHash]))
            {
                try
                {
                    /*
                     * Getting object db tables info
                     */
                    $tablesData = $oDb->fetchAll("SHOW TABLE STATUS");
                }
                catch (Exception $e)
                {
                    $canConnect = false;
                }

                if(!empty($tablesData))
                    foreach ($tablesData as $k=>$v)
                        $tables[$oDbHash][$v['Name']] = array(
                            'rows'=>$v['Rows'],
                            'data_length'=>$v['Data_length'],
                            'index_length'=>$v['Index_length']
                        );

                unset($tablesData);
            }

            if(Registry::get('main', 'config')->get('orm_innodb_real_rows_count') && strtolower($config['engine']) === 'innodb' && $builder->tableExists($oModel->table()))
            {
                /*
                 * Real rows count for innodb tables
                 */
                $tables[$oDbHash][$objectTable]['rows'] = $oModel->getCount();
            }

            if(isset($tables[$oDbHash][$objectTable]))
            {
                $records = $tables[$oDbHash][$objectTable]['rows'];
                $dataLength = Utils::formatFileSize($tables[$oDbHash][$objectTable]['data_length']);
                $indexLength = Utils::formatFileSize($tables[$oDbHash][$objectTable]['index_length']);
                $size = Utils::formatFileSize($tables[$oDbHash][$objectTable]['data_length'] + $tables[$oDbHash][$objectTable]['index_length']);
            }

            $title = '';
            $saveHistory = true;
            $linktitle = '';

            if(isset($config['title']) && !empty($config['title']))
                $title = $config['title'];

            if(isset($config['link_title']) && !empty($config['link_title']))
                $linktitle = $config['link_title'];

            if(isset($config['save_history']) && !$config['save_history'])
                $saveHistory = false;

            $hasBroken = false;

            if($builder->hasBrokenLinks())
                $hasBroken = true;

            $data[] = array(
                'name'=>$objectName,
                'table'=>$objectTable,
                'engine'=>$config['engine'],
                'vc'=>$config['rev_control'],
                'fields'=>sizeof($config['fields']),
                'records'=>number_format($records,0,'.',' '),
                'title'=>$title,
                'link_title'=>$linktitle,
                'rev_control'=>$config['rev_control'],
                'save_history'=>$saveHistory,
                'data_size'=>$dataLength,
                'index_size'=>$indexLength,
                'size'=>$size,
                'system'=>$configObject->isSystem(),
                'validdb'=>$builder->validate(),
                'broken'=>$hasBroken,
                'db_host'=>$oDbConfig['host'] ,
                'db_name'=>$oDbConfig['dbname'],
                'locked'=>$config['locked'],
                'readonly'=>$config['readonly'],
                'can_connect'=>$canConnect,
                'primary_key'=>$configObject->getPrimaryKey(),
                'connection'=>$config['connection']
            );

        }
        return $data;
    }

    /**
     * Get DB Objects list
     */
    public function listAction()
    {
        $db = Model::getDefaultDbManager()->getDbConnection('default');
        $data = self::getDbStats($db);

        if(Request::post('hideSysObj', 'boolean', false)){
            foreach ($data as $k => $v)
                if($v['system'])
                    unset($data[$k]);
            sort($data);
        }
        Response::jsonSuccess($data);
    }

    /**
     * Get object fields
     */
    public function fieldsAction()
    {
        $object = Request::post('object', 'string', false);

        if(!$object)
            Response::jsonError($this->_lang->INVALID_VALUE);

        try{
            $objectConfig = Db_Object_Config::getInstance($object);
        }catch (Exception $e){
            Response::jsonError($this->_lang->INVALID_VALUE);
        }

        $builder = new Db_Object_Builder($object);
        $brokenFields = $builder->hasBrokenLinks();

        $fieldsCfg = $objectConfig->getFieldsConfig();

        foreach ($fieldsCfg as $k=>&$v)
        {
            $v['name'] = $k;
            $v['unique'] = $objectConfig->isUnique($k);

            if(isset($brokenFields[$k]))
                $v['broken'] = true;
            else
                $v['broken'] = false;

            if(isset($v['type']) && !empty($v['type']))
            {
                if($v['type'] == 'link')
                {
                    $v['type'].= ' ('.$v['link_config']['object'].')';
                    $v['link_type'] = $v['link_config']['link_type'];
                    $v['object'] = $v['link_config']['object'];
                    unset($v['link_config']);
                }
                continue;
            }

            $v['type'] =  $v['db_type'];

            if(in_array($v['db_type'], Db_Object_Builder::$charTypes , true)){
                $v['type'].=' ('.$v['db_len'].')';
            }elseif (in_array($v['db_type'], Db_Object_Builder::$floatTypes , true)){
                $v['type'].=' ('.$v['db_scale'].','.$v['db_precision'].')';
            }
        }unset($v);
        Response::jsonArray(array_values($fieldsCfg));
    }

    /**
     * Get object indexes
     */
    public function indexesAction()
    {
        $object = Request::post('object', 'string', false);

        if(!$object)
            Response::jsonError($this->_lang->INVALID_VALUE);

        try{
            $objectConfig = Db_Object_Config::getInstance($object);
        }catch (Exception $e){
            Response::jsonError($this->_lang->INVALID_VALUE);
        }

        $indexCfg = $objectConfig->getIndexesConfig();

        foreach ($indexCfg as $k=>&$v){
            $v['columns'] = implode(', ', $v['columns']);
            $v['name'] = $k;
        }unset($v);

        Response::jsonArray(array_values($indexCfg));
    }

    /**
     * Load index config action
     */
    public function loadIndexAction()
    {
        $object = Request::post('object', 'string',false);
        $index = Request::post('index', 'string',false);

        if(!$object || !$index)
            Response::jsonError($this->_lang->INVALID_VALUE);

        $manager = new Backend_Orm_Manager();
        $indexConfig = $manager->getIndexConfig($object, $index);

        if($indexConfig === false)
            Response::jsonError($this->_lang->INVALID_VALUE);
        else
            Response::jsonSuccess($indexConfig);

    }

    /**
     * Load Db Object info
     */
    public function loadAction()
    {
        $object = Request::post('object', 'string',false);
        if($object === false)
            Response::jsonError($this->_lang->INVALID_VALUE);

        try {
            $config = Db_Object_Config::getInstance($object);
            $info = $config->__toArray();
            $info['name'] = $object;
            $info['use_acl'] = false;

            if($info['acl'])
                $info['use_acl'] = true;

            unset($info['fields']);
            Response::jsonSuccess($info);
        }catch (Exception $e){
            Response::jsonError($this->_lang->INVALID_VALUE);
        }
    }

    /**
     * Save Object indexes
     * @todo validate index columns, check if they exists in config
     */
    public function saveIndexAction()
    {
        $this->_checkCanEdit();

        $object =  Request::post('object', 'string', false);
        $index =   Request::post('index', 'string', false);
        $columns = Request::post('columns', 'array', array());
        $name = Request::post('name', 'string', false);
        $unique = Request::post('unique', 'boolean', false);
        $fulltext =Request::post('fulltext', 'boolean', false);

        if(!$object)
            Response::jsonError($this->_lang->WRONG_REQUEST.' code 1');

        if(!$name)
            Response::jsonError($this->_lang->FILL_FORM , array(array('id'=>'name','msg'=>$this->_lang->CANT_BE_EMPTY)));

        try{
            $objectCfg = Db_Object_Config::getInstance($object);
        }catch (Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST .' code 2');
        }

        $indexData = array(
            'columns'=>$columns,
            'unique'=>$unique,
            'fulltext'=>$fulltext,
            'PRIMARY'=>false
        );

        $indexes = $objectCfg->getIndexesConfig();

        if($index !== $name && array_key_exists((string)$name, $indexes))
            Response::jsonError($this->_lang->FILL_FORM , array(array('id'=>'name','msg'=>$this->_lang->SB_UNIQUE)));

        if($index!=$name)
            $objectCfg->removeIndex($index);

        $objectCfg->setIndexConfig($name, $indexData);

        if($objectCfg->save())
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_WRITE_FS);
    }

    /**
     * Delete object field
     */
    public function deleteFieldAction()
    {
        $this->_checkCanDelete();

        $object =  Request::post('object', 'string', false);
        $field =   Request::post('name', 'string', false);

        $manager = new Backend_Orm_Manager();
        $result = $manager->removeField($object, $field);

        switch ($result)
        {
            case 0 :
                Response::jsonSuccess();
                break;
            case Backend_Orm_Manager::ERROR_INVALID_FIELD:
            case Backend_Orm_Manager::ERROR_INVALID_OBJECT:
                Response::jsonError($this->_lang->WRONG_REQUEST);
                break;
            case Backend_Orm_Manager::ERROR_FS_LOCALISATION:
                Response::jsonError($this->_lang->CANT_WRITE_FS . ' ('.$this->_lang->LOCALIZATION_FILE.')');
                break;
            case Backend_Orm_Manager::ERROR_FS:
                Response::jsonError($this->_lang->CANT_WRITE_FS);
                break;
            default:
                Response::jsonError($this->_lang->CANT_EXEC);
        }
    }

    /**
     * Delete object index
     */
    public function deleteIndexAction()
    {
        $this->_checkCanDelete();

        $object =  Request::post('object', 'string', false);
        $index =   Request::post('name', 'string', false);

        if(!$object || !$index)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        try{
            $objectCfg = Db_Object_Config::getInstance($object);
        }catch (Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST .' code 2');
        }

        $objectCfg->removeIndex($index);

        if($objectCfg->save())
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_WRITE_FS);
    }

    /**
     * Create / Update Db object
     */
    public function saveAction()
    {
        $this->_checkCanEdit();

        $recordId = Request::post('record_id', 'string', '0');
        $revControl = Request::post('rev_control', 'boolean', false);
        $saveHistory = Request::post('save_history', 'boolean', false);
        $linkTitle = Request::post('link_title', 'string', '');
        $name = Request::post('name', 'string', '');
        $disableKeys = Request::post('disable_keys', 'boolean', false);

        $pimaryKey = Request::post('primary_key', 'string', 'id');

        $connection = Request::post('connection', 'string', '');
        $slaveConnection = Request::post('slave_connection', 'string', '');
        $readonly = Request::post('readonly', 'boolean', false);
        $locked = Request::post('locked', 'boolean', false);

        $usePrefix = Request::post('use_db_prefix', 'boolean', false);
        $useAcl = Request::post('use_acl', 'boolean', false);
        $acl =  Request::post('acl', 'string', false);

        $detalization = Request::post('log_detalization' , 'string' , 'default');

        if( $detalization!=='extended'){
            $detalization = 'default';
        }

        $parentObject = Request::post('parent_object' , 'string', '');

        $reqStrings = array('name','title','table', 'engine','connection');
        $errors = array();
        $data = array();


        foreach ($reqStrings as $v)
        {
            $value = Request::post($v, 'string', '');

            if(!strlen($value))
                $errors[] = array('id'=>$v ,'msg'=>$this->_lang->CANT_BE_EMPTY);

            if($v!=='name')
                $data[$v] = $value;
        }

        // check ACL Adapter
        if($useAcl && (empty($acl) || !class_exists($acl)))
            $errors[] = array('id'=>'acl' ,'msg'=>$this->_lang->INVALID_VALUE);


        if(!empty($errors))
            Response::jsonError($this->_lang->FILL_FORM , $errors);

        if($useAcl)
            $data['acl'] = $acl;
        else
            $data['acl'] = false;

        $data['parent_object'] = $parentObject;
        $data['rev_control'] = $revControl;
        $data['save_history'] = $saveHistory;
        $data['link_title'] = $linkTitle;
        $data['disable_keys'] = $disableKeys;
        $data['readonly'] = $readonly;
        $data['locked'] = $locked;
        $data['primary_key'] = $pimaryKey;
        $data['use_db_prefix'] = $usePrefix;
        $data['slave_connection'] = $slaveConnection;
        $data['connection'] = $connection;
        $data['log_detalization'] = $detalization;

        $name = strtolower($name);

        if($recordId === ''){
            $this->_createObject($name , $data);
        }else{
            $this->_updateObject($recordId, $name, $data);
        }
    }

    /**
     * Create Db_Object
     * @param string $name - object name
     * @param array $data - object config
     */
    protected function _createObject($name , array $data)
    {
        $usePrefix = $data['use_db_prefix'];
        $connectionManager = new Db_Manager($this->_configMain);
        $connection = $connectionManager->getDbConnection($data['connection']);
        $connectionCfg = $connectionManager->getDbConfig($data['connection']);

        //$db = Model::getGlobalDbConnection();
        $db = $connection;
        $tables = $db->listTables();
        $oConfigPath = Db_Object_Config::getConfigPath();
        $configDir  = Config::storage()->getWrite() . $oConfigPath;

        $tableName = $data['table'];

        if($usePrefix){
            $tableName = $connectionCfg->get('prefix') . $tableName;
        }

        if(in_array($tableName, $tables ,true)){
            Response::jsonError($this->_lang->FILL_FORM , array(array('id'=>'table','msg'=>$this->_lang->SB_UNIQUE)));
        }

        if(file_exists($configDir . strtolower($name).'.php')) {
            Response::jsonError($this->_lang->FILL_FORM, array(array('id' => 'name', 'msg' => $this->_lang->SB_UNIQUE)));
        }

        if(!is_dir($configDir) && !@mkdir($configDir, 0655, true)){
            Response::jsonError($this->_lang->CANT_WRITE_FS.' '.$configDir);
        }

        /*
         * Write object config
         */
        if(!Config_File_Array::create($configDir. $name . '.php'))
            Response::jsonError($this->_lang->CANT_WRITE_FS . ' ' . $configDir . $name . '.php');

        $cfg = Config::storage()->get($oConfigPath. strtolower($name).'.php' , false , false);
        /*
         * Add fields config
         */
        $data['fields'] = array();

        $cfg->setData($data);
        $cfg->save();

        try{
            $cfg = Db_Object_Config::getInstance($name);
            $cfg->setObjectTitle($data['title']);

            if(!$cfg->save())
                Response::jsonError($this->_lang->CANT_WRITE_FS);

            /*
             * Build database
            */
            $builder = new Db_Object_Builder($name);
            $builder->build();

        }catch (Exception $e){
            Response::jsonError($this->_lang->CANT_EXEC . 'code 2');
        }
        Response::jsonSuccess();
    }

    protected function _updateObject($recordId , $name , array $data)
    {
        $dataDir = Config::storage()->getWrite() . $this->_configMain->get('object_configs');
        $objectConfigPath = $dataDir . $recordId.'.php';

        if(!is_writable($dataDir))
            Response::jsonError($this->_lang->get('CANT_WRITE_FS') . ' ' . $dataDir);

        if(file_exists($objectConfigPath) && !is_writable($objectConfigPath))
            Response::jsonError($this->_lang->get('CANT_WRITE_FS') . ' ' . $objectConfigPath);

        /*
         * Rename object
        */
        if($recordId!=$name)
        {
            $this->_renameObject($recordId, $name);
        }

        try {
            $config = Db_Object_Config::getInstance($name);
        }catch (Exception $e){
            Response::jsonError($this->_lang->INVALID_VALUE);
        }

        $builder = new Db_Object_Builder($name);

        /*
         * Rename Db Table
         */
        if($config->get('table')!==$data['table'])
        {
            if($builder->tableExists($data['table'] , true))
                Response::jsonError($this->_lang->FILL_FORM , array(array('id'=>'table','msg'=>$this->_lang->SB_UNIQUE)));

            if(!$builder->renameTable($data['table']))
                Response::jsonError($this->_lang->CANT_RENAME_TABLE);
        }

        /*
         * Check and apply changes for DB Table engine
         */
        if($config->get('engine')!==$data['engine'])
        {
            $err = $builder->checkEngineCompatibility($data['engine']);

            if($err !== true)
                Response::jsonError($this->_lang->CANT_EXEC . ' ', $err);

            if(!$builder->changeTableEngine($data['engine']))
            {
                $errors = $builder->getErrors();
                if(!empty($errors))
                    $errors = implode(' <br>' , $errors);
                Response::jsonError($this->_lang->CANT_EXEC . ' ' . $errors);
            }
        }

        $data['fields'] = $config->getFieldsConfig(false);

        $config->setData($data);
        $config->setObjectTitle($data['title']);

        if(!$config->save())
            Response::jsonError($this->_lang->CANT_WRITE_FS);

        Response::jsonSuccess();
    }

    protected function _renameObject($oldName , $newName)
    {

        $newFileName = $this->_configMain->get('object_configs').$newName.'.php';
        //$oldFileName = $this->_configMain->get('object_configs').$oldName.'.php';

        if(file_exists($newFileName))
            Response::jsonError($this->_lang->FILL_FORM ,array(array('id'=>'name','msg'=>$this->_lang->SB_UNIQUE)));

        $manager = new Backend_Orm_Manager();
        $renameResult = $manager->renameObject($this->_configMain['object_configs'] , $oldName , $newName);

        switch ($renameResult)
        {
            case 0:
                break;
            case Backend_Orm_Manager::ERROR_FS:
                Response::jsonError($this->_lang->CANT_WRITE_FS);
                break;
            case Backend_Orm_Manager::ERROR_FS_LOCALISATION:
                Response::jsonError($this->_lang->CANT_WRITE_FS . ' ('.$this->_lang->LOCALIZATION_FILE.')');
                break;
            default:
                Response::jsonError($this->_lang->CANT_EXEC .' code 5');
        }
        /*
         * Clear cache
         */
        Config::resetCache();
    }

    /**
     * Validate object action
     */
    public function validateAction()
    {
        $engineUpdate = false;

        $name = Request::post('name', 'string', false);

        if(!$name)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $objectConfig = Db_Object_Config::getInstance($name);

        // Check ACL permissions
        $acl = $objectConfig->getAcl();
        if($acl){
            if(!$acl->can(Db_Object_Acl::ACCESS_CREATE , $name) || !$acl->can(Db_Object_Acl::ACCESS_VIEW , $name)){
                Response::jsonError($this->_lang->get('ACL_ACCESS_DENIED'));
            }
        }

        try {
            $obj = new Db_Object($name);
        } catch (Exception $e){
            Response::jsonError($this->_lang->get('CANT_GET_VALIDATE_INFO'));
        }

        $builder = new Db_Object_Builder($name);
        $tableExists = $builder->tableExists();

        $colUpd = array();
        $indUpd = array();
        $keyUpd = array();

        if($tableExists){
            $colUpd =  $builder->prepareColumnUpdates();
            $indUpd =  $builder->prepareIndexUpdates();
            $keyUpd =  $builder->prepareKeysUpdate();
            $engineUpdate = $builder->prepareEngineUpdate();
        }

        $objects = $builder->getObjectsUpdatesInfo();

        if(empty($colUpd) && empty($indUpd) && empty($keyUpd) && $tableExists && !$engineUpdate && empty($objects))
            Response::jsonSuccess(array(),array('nothingToDo'=>true));

        $template = new Template();
        $template->disableCache();
        $template->engineUpdate = $engineUpdate;
        $template->columns = $colUpd;
        $template->indexes = $indUpd;
        $template->objects = $objects;
        $template->keys = $keyUpd;
        $template->tableExists = $tableExists;
        $template->tableName = $obj->getTable();
        $template->lang = $this->_lang;

        $msg = $template->render(Application::getTemplatesPath() . 'orm_validate_msg.php');

        Response::jsonSuccess(array(),array('text'=>$msg,'nothingToDo'=>false));
    }

    /**
     * Build object action
     */
    public function buildAction()
    {
        $this->_checkCanEdit();

        $name = Request::post('name', 'string', false);

        if(!$name)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if(!Db_Object_Config::configExists($name))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $builder = new Db_Object_Builder($name);

        if(!$builder->build() || !$builder->buildForeignKeys())
            Response::jsonError($this->_lang->CANT_EXEC.' ' . implode(',', $builder->getErrors()));

        Response::jsonSuccess();

    }

    /**
     * Build all objects action
     */
    public function buildAllAction()
    {
        $this->_checkCanEdit();

        $names = Request::post('names', 'array', false);

        if(empty($names))
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        $flag = false;

        /*
         * remove foreign keys
         */
        foreach ($names as $name)
        {
            try{
                $builder = new Db_Object_Builder($name);
                if(!$builder->buildForeignKeys(true , false))
                    $flag = true;
            }catch(Exception $e){
                $flag = true;
            }
        }

        if(Db_Object_Builder::foreignKeys())
        {
            /*
             * build only fields
             */
            foreach ($names as $name)
            {
                try{
                    $builder = new Db_Object_Builder($name);
                    $builder->build(false);
                }catch(Exception $e){
                    $flag = true;
                }
            }

            /*
             * Add foreign keys
             */
            foreach ($names as $name)
            {
                try{
                    $builder = new Db_Object_Builder($name);
                    if(!$builder->buildForeignKeys(true , true))
                        $flag = true;
                }catch(Exception $e){
                    $flag = true;
                }
            }

        }else{
            foreach ($names as $name)
            {
                try{
                    $builder = new Db_Object_Builder($name);
                    $builder->build();
                }catch(Exception $e){
                    $flag = true;
                }
            }
        }

        if ($flag)
            Response::jsonError($this->_lang->CANT_EXEC);
        else
            Response::jsonSuccess();
    }

    /**
     * Load Field config
     */
    public function loadFieldAction()
    {
        $object = Request::post('object', 'string',false);
        $field = Request::post('field', 'string',false);

        if(!$object || !$field)
            Response::jsonError($this->_lang->INVALID_VALUE);

        $manager = new Backend_Orm_Manager();
        $result = $manager->getFieldConfig($object , $field);

        if(!$result)
            Response::jsonError($this->_lang->INVALID_VALUE);

        Response::jsonSuccess($result);
    }

    /**
     * Save field configuration options
     */
    public function saveFieldAction()
    {
        $this->_checkCanEdit();

        $manager = new Backend_Orm_Manager();

        $object = Request::post('objectName', 'string', false);
        $objectField = Request::post('objectField', 'string', false);
        $name = Request::post('name', 'string', false);

        if(!$object)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if(!$name)
            Response::jsonError($this->_lang->FILL_FORM , array(array('id'=>'name','msg'=>$this->_lang->CANT_BE_EMPTY)));

        try{
            /**
             * @var Db_Object_Config
             */
            $objectCfg = Db_Object_Config::getInstance($object);
        }catch (Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST .' code 2');
        }

        $oFields = array_keys($objectCfg->getFieldsConfig());

        if($objectField !== $name && in_array($name, $oFields , true))
            Response::jsonError($this->_lang->FILL_FORM , array(array('id'=>'name','msg'=>$this->_lang->SB_UNIQUE)));

        $unique = Request::post('unique', 'str', '');
        $newConfig = array();
        $newConfig['type'] = Request::post('type', 'str', '');
        $newConfig['title']= Request::post('title', 'str', '');
        $newConfig['unique'] = ($unique === false) ? '' : $unique;
        $newConfig['db_isNull'] = Request::post('db_isNull', 'boolean', false);
        $newConfig['required'] = Request::post('required', 'boolean', false);
        $newConfig['validator'] = Request::post('validator', 'string', '');

        if($newConfig['type']=='link')
        {
            if($newConfig['db_isNull'])
                $newConfig['required'] = false;
            /**
             * Process link field
             */
            $newConfig['link_config']['link_type'] = Request::post('link_type', 'str', 'object');

            if($newConfig['link_config']['link_type'] === Db_Object_Config::LINK_DICTIONARY)
            {
                $newConfig['link_config']['object'] = Request::post('dictionary', 'str', '');
                $newConfig['db_type'] = 'varchar';
                $newConfig['db_len'] = 255;
                $newConfig['db_isNull'] = false;

                if($newConfig['required']){
                    $newConfig['db_default'] = false;
                }else{
                    $newConfig['db_default'] = '';
                }
            }
            else
            {
                $linkedObject = Request::post('object', 'string', false);
                if(!$linkedObject)
                    Response::jsonError($this->_lang->FILL_FORM , array(array('id'=>'object','msg'=>$this->_lang->CANT_BE_EMPTY)));

                try {
                    $cf = Db_Object_Config::getInstance($linkedObject);
                }catch(Exception $e){
                    Response::jsonError($this->_lang->FILL_FORM , array(array('id'=>'object','msg'=>$this->_lang->INVALID_VALUE)));
                }
                $newConfig['link_config']['object'] = $linkedObject;

                switch ($newConfig['link_config']['link_type'])
                {
                    case Db_Object_Config::LINK_OBJECT_LIST:

                        $newConfig['link_config']['relations_type'] = Request::post('relations_type' , 'string' , false);
                        if(!in_array($newConfig['link_config']['relations_type'] , array('polymorphic','many_to_many') , true)){
                            $newConfig['link_config']['relations_type'] = 'polymorphic';
                        }

                        $newConfig['db_type'] = 'longtext';
                        $newConfig['db_isNull'] = false;
                        $newConfig['db_default'] = '';
                        break;

                    case Db_Object_Config::LINK_OBJECT:
                        $newConfig['db_isNull'] = (boolean) !$newConfig['required'];
                        $newConfig['db_type'] ='bigint';
                        $newConfig['db_default'] = false;
                        $newConfig['db_unsigned'] = true;
                        break;
                }
            }

        }elseif($newConfig['type']=='encrypted') {

            $setDefault = Request::post('set_default', 'boolean', false);

            if(!$setDefault){
                $newConfig['db_default'] = false;
            }else{
                $newConfig['db_default'] = Request::post('db_default', 'string', false);
            }

            $newConfig['db_type'] = 'longtext';
            $newConfig['is_search'] = false;
            $newConfig['allow_html'] = false;

        }else{
            $setDefault = Request::post('set_default', 'boolean', false);
            /*
             * Process std field
             */
            $newConfig['db_type'] = Request::post('db_type', 'str', 'false');
            if(!$newConfig['db_type'])
                Response::jsonError($this->_lang->FILL_FORM , array(array('id'=>'db_type','msg'=>$this->_lang->CANT_BE_EMPTY)));

            if($newConfig['db_type']=='bool' || $newConfig['db_type']=='boolean'){
                /*
                 * boolean
                 */
                $newConfig['required'] = false;
                $newConfig['db_default'] = (integer)Request::post('db_default', 'bool', false);
            }elseif(in_array($newConfig['db_type'] , Db_Object_Builder::$intTypes , true)){
                /*
                 * integer
                 */
                $newConfig['db_default'] = Request::post('db_default', 'integer', false);
                $newConfig['db_unsigned'] = Request::post('db_unsigned', 'bool', false);
            }elseif(in_array($newConfig['db_type'], Db_Object_Builder::$floatTypes)){
                /*
                 * float
                 */
                $newConfig['db_default'] = Request::post('db_default', 'float', false);
                $newConfig['db_unsigned'] = Request::post('db_unsigned', 'bool', false);
                $newConfig['db_scale'] = Request::post('db_scale', 'integer', 0);
                $newConfig['db_precision'] = Request::post('db_precision', 'integer', 0);
            }elseif(in_array($newConfig['db_type'] , Db_Object_Builder::$charTypes , true)){
                /*
                 * char
                 */
                $newConfig['db_default'] = Request::post('db_default', 'string', false);
                $newConfig['db_len'] = Request::post('db_len', 'integer', 255);
                $newConfig['is_search'] =Request::post('is_search', 'bool', false);
                $newConfig['allow_html'] =Request::post('allow_html', 'bool', false);
            }elseif(in_array($newConfig['db_type'] , Db_Object_Builder::$textTypes , true)){
                /*
                 * text
                 */
                $newConfig['db_default'] = Request::post('db_default', 'string', false);
                $newConfig['is_search'] =  Request::post('is_search', 'bool', false);
                $newConfig['allow_html'] = Request::post('allow_html', 'bool', false);

                if(!$newConfig['required'])
                    $newConfig['db_isNull'] = true;

            }elseif(in_array($newConfig['db_type'] , Db_Object_Builder::$dateTypes , true)){
                /*
                 * date
                 */
                if(!$newConfig['required'])
                    $newConfig['db_isNull'] = true;
            }
            else{
                Response::jsonError($this->_lang->FILL_FORM , array(array('id'=>'db_type','msg'=>$this->_lang->INVALID_VALUE)));
            }

            if(!$setDefault){
                $newConfig['db_default'] = false;
            }
        }

        /**
         * @todo Rename
         */
        if($objectField!=$name && !empty($objectField))
        {
            $objectCfg->setFieldConfig($objectField, $newConfig);
            $renameResult = $manager->renameField($objectCfg , $objectField , $name);

            switch ($renameResult)
            {
                case Backend_Orm_Manager::ERROR_EXEC:
                    Response::jsonError($this->_lang->CANT_EXEC);
                    break;
                case Backend_Orm_Manager::ERROR_FS_LOCALISATION:
                    Response::jsonError($this->_lang->CANT_WRITE_FS . ' ('.$this->_lang->LOCALIZATION_FILE.')');
                    break;
            }

        } else{
            $objectCfg->setFieldConfig($name, $newConfig);
            $objectCfg->fixConfig();
        }
        if($objectCfg->save()){
            $builder = new Db_Object_Builder($object);
            $builder->build();
            Response::jsonSuccess();
        }else{
            Response::jsonError($this->_lang->CANT_WRITE_FS);
        }
    }

    /**
     * Remove Db_Object from system
     */
    public function removeObjectAction()
    {
        $this->_checkCanDelete();

        $objectName = Request::post('objectName', 'string', false);
        $deleteTable = Request::post('delete_table', Filter::FILTER_BOOLEAN, false);

        if(!$objectName)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        try{
            $oConfig  = Db_Object_Config::getInstance($objectName);
            if($deleteTable && ($oConfig->isLocked() || $oConfig->isReadOnly())){
                Response::jsonError($this->_lang->DB_CANT_DELETE_LOCKED_TABLE);
            }
        }catch (Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }

        $manager = new Backend_Orm_Manager();
        $result = $manager->removeObject($objectName , $deleteTable);

        switch ($result){
            case 0 :  Response::jsonSuccess();
                break;
            case Backend_Orm_Manager::ERROR_FS:
                Response::jsonError($this->_lang->CANT_WRITE_FS);
                break;
            case Backend_Orm_Manager::ERROR_DB:
                Response::jsonError($this->_lang->CANT_WRITE_DB);
                break;
            case Backend_Orm_Manager::ERROR_FS_LOCALISATION:
                Response::jsonError($this->_lang->CANT_WRITE_FS . ' ('.$this->_lang->LOCALIZATION_FILE.')');
                break;
            case Backend_Orm_Manager::ERROR_HAS_LINKS:
                Response::jsonError($this->_lang->MSG_ORM_CAND_DELETE_LINKED);
                break;
            default:
                Response::jsonError($this->_lang->CANT_EXEC);
        }
    }

    /**
     * Get data for UML diagram
     */
    public function getUmlDataAction()
    {
        $config = Config::storage()->get(self::UML_MAP_CFG,true,false);

        $items = $config->get('items');

        $data = $field = array();
        $manager = new Db_Object_Manager();
        $names = $manager->getRegisteredObjects();

        $defaultX = 10;
        $defaultY = 10;

        foreach($names as $objectName)
        {
            $data[$objectName]['links'] = Db_Object_Config::getInstance($objectName)->getLinks();

            $objectConfig = Db_Object_Config::getInstance($objectName);
            $fields = $objectConfig->getFieldsConfig();

            foreach($fields as $fieldName => $fieldData)
            {
                $data[$objectName]['fields'][] = $fieldName;

                if(isset($items[$objectName]))
                {
                    $data[$objectName]['position'] = array('x'=>$items[$objectName]['x'],'y'=>$items[$objectName]['y']);
                }
                else
                {
                    $data[$objectName]['position'] = array('x'=>$defaultX , 'y'=>$defaultY);
                    $defaultX+=10;
                    $defaultY+=10;
                }
            }
            sort($data[$objectName]['fields']);
        }

        foreach($names as $objectName){
            foreach($data[$objectName]['links'] as $link => $link_value){
                $data[$link]['weight'] = ( !isset($data[$link]['weight']) ? 1 : $data[$link]['weight'] + 1 );
            }
            if(!isset($data[$objectName]['weight']))
                $data[$objectName]['weight'] = 0;
        }

        $fieldName = "weight";

        uasort( $data, function($a, $b) use($fieldName) {
            return strnatcmp( $b[$fieldName], $a[$fieldName] );
        } );

        $result = array(
            'mapWidth'=>$config->get('mapWidth'),
            'mapHeight'=>$config->get('mapHeight'),
            'items'=>$data
        );

        Response::jsonSuccess($result);
    }

    /**
     * Save object coordinates
     */
    public function saveUmlMapAction()
    {
        $this->_checkCanEdit();

        $map = Request::post('map', 'raw', '');

        if(!strlen($map))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $data = json_decode($map , true);

        $config = Config::storage()->get(self::UML_MAP_CFG,true, false);

        $config->set('items' , $data);

        if($config->save())
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_WRITE_FS);
    }

    /**
     * Dictionary Sub Controller
     */
    public function dictionaryAction()
    {
        $request = Request::getInstance();
        $router = new Backend_Router();
        $router->runController('Backend_Orm_Dictionary' , $request->getPart(3));
    }

    /**
     * Object data view sub controller
     */
    public function dataViewAction()
    {
        $request = Request::getInstance();
        $router = new Backend_Router();
        $router->runController('Backend_Orm_Dataview' , $request->getPart(3));
    }

    /**
     * Db connection sub controller
     */
    public function connectionsAction()
    {
        $request = Request::getInstance();
        $router = new Backend_Router();

        $router->runController('Backend_Orm_Connections_Controller' , $request->getPart(3));
    }

    /**
     * Logs Sub Controller
     */
    public function logAction()
    {
        $request = Request::getInstance();
        $router = new Backend_Router();
        $router->runController('Backend_Orm_Log' , $request->getPart(3));
    }

    /**
     * Get list of database connections
     */
    public function connectionsListAction()
    {
        $manager = new Backend_Orm_Connections_Manager($this->_configMain->get('db_configs'));
        $list = $manager->getConnections(0);
        $data = array();
        if(!empty($list))
        {
            foreach($list as $k=>$v)
            {
                $data[] = array('id'=> $k);
            }
        }
        Response::jsonSuccess($data);
    }

    /**
     * Get connection types (prod , dev , test ... etc)
     */
    public function connectionTypesAction()
    {
        $data = array();
        foreach ($this->_configMain->get('db_configs') as $k=>$v){
            $data[]= array('id'=>$k , 'title'=>$this->_lang->get($v['title']));
        }
        Response::jsonSuccess($data);
    }

    /*
     * Get list of field validators
     */
    public function listValidatorsAction()
    {
        $validators = array(array('id'=>'','title'=>'---'));
        $files = File::scanFiles('./dvelum/library/Validator', array('.php'), false, File::Files_Only);

        foreach ($files as $v)
        {
            $name = substr(basename($v), 0, -4);
            if($name != 'Interface')
                $validators[] = array('id'=>'Validator_'.$name, 'title'=>$name);
        }

        Response::jsonSuccess($validators);
    }

    /**
     * Dev. method. Compile JavaScript sources
     */
    public function compileAction()
    {
        $sources = array(
            'js/app/system/orm/panel.js',
            'js/app/system/orm/dataGrid.js',
            'js/app/system/orm/objectWindow.js',
            'js/app/system/orm/fieldWindow.js',
            'js/app/system/orm/indexWindow.js',
            'js/app/system/orm/dictionaryWindow.js',
            'js/app/system/orm/objectsMapWindow.js',
            'js/app/system/orm/dataViewWindow.js',
            'js/app/system/orm/objectField.js',
            'js/app/system/orm/connections.js',
            'js/app/system/orm/logWindow.js',
            'js/app/system/orm/import.js',
            'js/app/system/orm/taskStatusWindow.js'
        );

        if(!$this->_configMain->get('development')){
            die('Use development mode');
        }

        $s = '';
        $totalSize = 0;

        $wwwPath = $this->_configMain->get('wwwpath');
        foreach ($sources as $filePath){
            $s.=file_get_contents($wwwPath.$filePath)."\n";
            $totalSize+=filesize($wwwPath.$filePath);
        }

        $time = microtime(true);
        file_put_contents($wwwPath.'js/app/system/ORM.js', Code_Js_Minify::minify($s));
        echo '
			Compilation time: '.number_format(microtime(true)-$time,5).' sec<br>
			Files compiled: '.sizeof($sources).' <br>
			Total size: '.Utils::formatFileSize($totalSize).'<br>
			Compiled File size: '.Utils::formatFileSize(filesize($wwwPath.'js/app/system/ORM.js')).' <br>
		';
        exit;
    }

    /**
     * Get list of ACL adapters
     */
    public function listAclAction()
    {
        $list = array(array('id'=>'','title'=>'---'));
        $files = File::scanFiles('./dvelum/app/Acl', array('.php'), true, File::Files_Only);
        foreach ($files as $v){
            $path = str_replace('./dvelum/app/', '', $v);
            $name = Utils::classFromPath($path);
            $list[] = array('id'=>$name,'title'=>$name);
        }
        Response::jsonSuccess($list);
    }

    /**
     * Check and fix config files
     */
    public function fixConfigsAction()
    {
        $manager = new Db_Object_Manager();
        $names = $manager->getRegisteredObjects();
        foreach ($names as $objectName)
        {
            $cfg = Db_Object_Config::getInstance($objectName);
            $cfg->save();
        }
        Response::jsonSuccess();
    }

    /**
     * Encrypt object data (background)
     */
    public function encryptDataAction()
    {
        $this->_checkCanEdit();
        $object = Request::post('object' , 'string' , false);

        if(!$object || !Db_Object_Config::configExists($object)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        $container = $this->encryptContainerPrefix . $object;

        $objectModel = Model::factory($object);
        $taskModel = Model::factory('bgtask');
        $signalModel = Model::factory('Bgtask_Signal');

        //disable profiling in dev mode
        if($this->_configMain->get('development')) {
            $taskModel->getDbConnection()->getProfiler()->setEnabled(false);
            $signalModel->getDbConnection()->getProfiler()->setEnabled(false);
            $objectModel->getDbConnection()->getProfiler()->setEnabled(false);
        }

        $logger =  new Bgtask_Log_File($this->_configMain['task_log_path'] . $container .'_' . date('d_m_Y__H_i_s'));

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

    /**
     * Decrypt object data (background)
     */
    public function decryptDataAction()
    {
        $this->_checkCanEdit();
        $object = Request::post('object' , 'string' , false);

        if(!$object || !Db_Object_Config::configExists($object)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        $container = $this->decryptContainerPrefix . $object;

        $objectModel = Model::factory($object);
        $taskModel = Model::factory('bgtask');
        $signalModel = Model::factory('Bgtask_Signal');

        //disable profiling in dev mode
        if($this->_configMain->get('development')) {
            $taskModel->getDbConnection()->getProfiler()->setEnabled(false);
            $signalModel->getDbConnection()->getProfiler()->setEnabled(false);
            $objectModel->getDbConnection()->getProfiler()->setEnabled(false);
        }

        $logger =  new Bgtask_Log_File($this->_configMain['task_log_path'] . $container .'_' . date('d_m_Y__H_i_s'));

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
     * Check background process status
     */
    public function taskStatAction()
    {
        $object = Request::post('object' , 'string' , false);
        $type = Request::post('type' , 'string' , false);

        if(!$object || ! $type)
            Response::jsonError();

        switch($type){
            case 'encrypt':
                $container = $this->encryptContainerPrefix . $object;
                break;
            case 'decrypt':
                $container = $this->decryptContainerPrefix . $object;
                break;
            default: Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        $session = Store_Session::getInstance();

        if(!$session->keyExists($container)){
            Response::jsonError();
        }

        $pid = $session->get($container);
        $taskModel = Model::factory('bgtask');
        $statusData = $taskModel->getItem($pid);

        if(empty($statusData))
            Response::jsonError($this->_lang->get('CANT_EXEC'));

        Response::jsonSuccess(array(
            'status' =>  $statusData['status'],
            'op_total' =>  $statusData['op_total'],
            'op_finished' =>  $statusData['op_finished']
        ));
    }

    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $version = Config::storage()->get('versions.php')->get('orm');
        $dbConfigs = array();

        foreach ($this->_configMain->get('db_configs') as $k=>$v){
            $dbConfigs[]= array('id'=>$k , 'title'=>$this->_lang->get($v['title']));
        }

        //tooltips
        $lPath = $this->_configMain->get('language').'/orm.php';
        Lang::addDictionaryLoader('orm_tooltips', $lPath, Config::File_Array);

        $projectData['includes']['js'][] = $this->_resource->cacheJs('
           var useForeignKeys = '.((integer) $this->_configMain['foreign_keys']).';
           var dbConfigsList = '.json_encode($dbConfigs).';
           var ormTooltips = '.Lang::lang('orm_tooltips')->getJson().';
        ');

        $projectData['includes']['js'][] = '/js/lib/uml/raphael.js';
        $projectData['includes']['js'][] = '/js/lib/uml/joint.js';
        $projectData['includes']['js'][] = '/js/lib/uml/joint.dia.js';
        $projectData['includes']['js'][] = '/js/lib/uml/joint.dia.uml.js';
        $projectData['includes']['js'][] = '/js/app/system/ORM.js?v='.$version;

        /*
         * Module bootstrap
         */
        if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}
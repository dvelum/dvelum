<?php
/**
 * ORM UI Controller
 */
use Dvelum\Config;
use Dvelum\Model;
use Dvelum\Orm;

class Backend_Orm_Controller extends \Dvelum\App\Backend\Orm\Controller{}

class Backend_Orm_ControllerOld extends \Dvelum\App\Backend\Controller
{
    const UML_MAP_CFG = 'umlMap.php';

    protected $encryptContainerPrefix = 'encrypt_';
    protected $decryptContainerPrefix = 'decrypt_';

    public function __construct()
    {
        parent::__construct();

        Orm\Object\Builder::writeLog($this->appConfig['use_orm_build_log']);
        Orm\Object\Builder::setLogPrefix($this->appConfig['development_version'].'_build_log.sql');
        Orm\Object\Builder::setLogsPath($this->appConfig['orm_log_path']);
    }

    public function indexAction()
    {
        $version = Config::storage()->get('versions.php')->get('orm');

        $res = \Dvelum\Resource::factory();
        $dbConfigs = array();

        foreach ($this->appConfig->get('db_configs') as $k=>$v){
            $dbConfigs[]= array('id'=>$k , 'title'=>$this->lang->get($v['title']));
        }
        //tooltips
        $lPath = $this->appConfig->get('language').'/orm.php';
        Lang::addDictionaryLoader('orm_tooltips', $lPath, Config\Factory::File_Array);



        $this->resource->addInlineJs('
          var canPublish =  '.((integer)$this->moduleAcl->canPublish($this->module)).';
          var canEdit = '.((integer)$this->moduleAcl->canEdit($this->module)).';
          var canDelete = '.((integer)$this->moduleAcl->canDelete($this->module)).';
          var useForeignKeys = '.((integer)$this->appConfig['foreign_keys']).';
          var canUseBackup = false;
          var dbConfigsList = '.json_encode($dbConfigs).';
        ');

        $this->resource->addRawJs('var ormTooltips = '.Lang::lang('orm_tooltips')->getJson().';');

        $res->addJs('/js/app/system/SearchPanel.js', 0);
        $res->addJs('/js/app/system/ORM.js?v='.$version, 2);

        $res->addJs('/js/app/system/EditWindow.js', 1);
        $res->addJs('/js/app/system/HistoryPanel.js', 1);
        $res->addJs('/js/app/system/ContentWindow.js', 1);
        $res->addJs('/js/app/system/RevisionPanel.js', 2);
        $res->addJs('/js/app/system/RelatedGridPanel.js', 2);

        $res->addJs('/js/app/system/SelectWindow.js', 2);
        $res->addJs('/js/app/system/ObjectLink.js', 3);

        Model::factory('Medialib')->includeScripts();
        $res->addCss('/css/system/joint.min.css', 1);
        $res->addJs('/js/lib/uml/lodash.min.js', 2);
        $res->addJs('/js/lib/uml/backbone-min.js', 3);
        $res->addJs('/js/lib/uml/joint.min.js', 4);
        $res->addJs('/js/app/system/crud/orm.js', 7);
    }

    /**
     * Get database statistics
     * @return array
     * @throws Exception
     */
    static public function getDbStats()
    {
        $data = [];

        /*
         * Getting list of objects
         */
        $manager = new Orm\Object\Manager();

        $names = $manager->getRegisteredObjects();

        if(empty($names))
            return [];

        $tables = [];

        /*
         * forming result set
         */
        foreach ($names as $objectName)
        {
            $configObject = Orm\Object\Config::factory($objectName);
            $objectModel = Model::factory($objectName);
            $config =  $configObject->__toArray();
            $objectTable = $objectModel->table();
            $builder = new Orm\Object\Builder($objectName);

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

            if(empty($builder->getBrokenLinks()))
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

        if($this->request->post('hideSysObj', 'boolean', false)){
            foreach ($data as $k => $v)
                if($v['system'])
                    unset($data[$k]);
            sort($data);
        }
       $this->response->success($data);
    }

    /**
     * Get object fields
     */
    public function fieldsAction()
    {
        $object = $this->request->post('object', 'string', false);

        if(!$object)
           $this->response->error($this->lang->INVALID_VALUE);

        try{
            $objectConfig = Orm\Object\Config::factory($object);
        }catch (Exception $e){
           $this->response->error($this->lang->INVALID_VALUE);
        }

        $builder = new Orm\Object\Builder($object);
        $brokenFields = $builder->getBrokenLinks();

        $fieldsCfg = $objectConfig->getFieldsConfig();

        foreach ($fieldsCfg as $k=>&$v)
        {
            $v= $v->__toArray();
            $v['name'] = $k;
            $v['unique'] = $objectConfig->getField($k)->isUnique();

            if(!empty($brokenFields) && isset($brokenFields[$k]))
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

            if(in_array($v['db_type'], Orm\Object\Builder::$charTypes , true)){
                $v['type'].=' ('.$v['db_len'].')';
            }elseif (in_array($v['db_type'], Orm\Object\Builder::$floatTypes , true)){
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
        $object = $this->request->post('object', 'string', false);

        if(!$object)
           $this->response->error($this->lang->INVALID_VALUE);

        try{
            $objectConfig = Orm\Object\Config::getInstance($object);
        }catch (Exception $e){
           $this->response->error($this->lang->INVALID_VALUE);
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
        $object = $this->request->post('object', 'string',false);
        $index = $this->request->post('index', 'string',false);

        if(!$object || !$index)
           $this->response->error($this->lang->INVALID_VALUE);

        $manager = new Backend_Orm_Manager();
        $indexConfig = $manager->getIndexConfig($object, $index);

        if($indexConfig === false)
           $this->response->error($this->lang->INVALID_VALUE);
        else
           $this->response->success($indexConfig);

    }

    /**
     * Load Db Object info
     */
    public function loadAction()
    {
        $object = $this->request->post('object', 'string',false);
        if($object === false)
           $this->response->error($this->lang->INVALID_VALUE);

        try {
            $config = Orm\Object\Config::factory($object);
            $info = $config->__toArray();
            $info['name'] = $object;
            $info['use_acl'] = false;

            if($info['acl'])
                $info['use_acl'] = true;

            unset($info['fields']);
           $this->response->success($info);
        }catch (Exception $e){
           $this->response->error($this->lang->INVALID_VALUE);
        }
    }

    /**
     * Save Object indexes
     * @todo validate index columns, check if they exists in config
     */
    public function saveIndexAction()
    {
        $this->checkCanEdit();

        $object =  $this->request->post('object', 'string', false);
        $index =   $this->request->post('index', 'string', false);
        $columns = $this->request->post('columns', 'array', array());
        $name = $this->request->post('name', 'string', false);
        $unique = $this->request->post('unique', 'boolean', false);
        $fulltext =$this->request->post('fulltext', 'boolean', false);

        if(!$object)
           $this->response->error($this->lang->WRONG_REQUEST.' code 1');

        if(!$name)
           $this->response->error($this->lang->FILL_FORM , array(array('id'=>'name','msg'=>$this->lang->CANT_BE_EMPTY)));

        try{
            $objectCfg = Orm\Object\Config::factory($object);
        }catch (Exception $e){
           $this->response->error($this->lang->WRONG_REQUEST .' code 2');
        }

        $indexData = array(
            'columns'=>$columns,
            'unique'=>$unique,
            'fulltext'=>$fulltext,
            'PRIMARY'=>false
        );

        $indexes = $objectCfg->getIndexesConfig();

        if($index !== $name && array_key_exists((string)$name, $indexes))
           $this->response->error($this->lang->FILL_FORM , array(array('id'=>'name','msg'=>$this->lang->SB_UNIQUE)));

        if($index!=$name)
            $objectCfg->removeIndex($index);

        $objectCfg->setIndexConfig($name, $indexData);

        if($objectCfg->save())
           $this->response->success();
        else
           $this->response->error($this->lang->CANT_WRITE_FS);
    }

    /**
     * Delete object field
     */
    public function deleteFieldAction()
    {
        $this->_checkCanDelete();

        $object =  $this->request->post('object', 'string', false);
        $field =   $this->request->post('name', 'string', false);

        $manager = new Backend_Orm_Manager();
        $result = $manager->removeField($object, $field);

        switch ($result)
        {
            case 0 :
               $this->response->success();
                break;
            case Backend_Orm_Manager::ERROR_INVALID_FIELD:
            case Backend_Orm_Manager::ERROR_INVALID_OBJECT:
               $this->response->error($this->lang->WRONG_REQUEST);
                break;
            case Backend_Orm_Manager::ERROR_FS_LOCALISATION:
               $this->response->error($this->lang->CANT_WRITE_FS . ' ('.$this->lang->LOCALIZATION_FILE.')');
                break;
            case Backend_Orm_Manager::ERROR_FS:
               $this->response->error($this->lang->CANT_WRITE_FS);
                break;
            default:
               $this->response->error($this->lang->CANT_EXEC);
        }
    }

    /**
     * Delete object index
     */
    public function deleteIndexAction()
    {
        $this->_checkCanDelete();

        $object =  $this->request->post('object', 'string', false);
        $index =   $this->request->post('name', 'string', false);

        if(!$object || !$index)
           $this->response->error($this->lang->WRONG_REQUEST);

        try{
            $objectCfg = Orm\Object\Config::factory($object);
        }catch (Exception $e){
           $this->response->error($this->lang->WRONG_REQUEST .' code 2');
        }

        $objectCfg->removeIndex($index);

        if($objectCfg->save())
           $this->response->success();
        else
           $this->response->error($this->lang->CANT_WRITE_FS);
    }

    /**
     * Create / Update Db object
     */
    public function saveAction()
    {
        $this->checkCanEdit();

        $recordId = $this->request->post('record_id', 'string', '0');
        $revControl = $this->request->post('rev_control', 'boolean', false);
        $saveHistory = $this->request->post('save_history', 'boolean', false);
        $linkTitle = $this->request->post('link_title', 'string', '');
        $name = $this->request->post('name', 'string', '');
        $disableKeys = $this->request->post('disable_keys', 'boolean', false);

        $pimaryKey = $this->request->post('primary_key', 'string', 'id');

        $connection = $this->request->post('connection', 'string', '');
        $slaveConnection = $this->request->post('slave_connection', 'string', '');
        $readonly = $this->request->post('readonly', 'boolean', false);
        $locked = $this->request->post('locked', 'boolean', false);

        $usePrefix = $this->request->post('use_db_prefix', 'boolean', false);
        $useAcl = $this->request->post('use_acl', 'boolean', false);
        $acl =  $this->request->post('acl', 'string', false);

        $detalization = $this->request->post('log_detalization' , 'string' , 'default');

        if( $detalization!=='extended'){
            $detalization = 'default';
        }

        $parentObject = $this->request->post('parent_object' , 'string', '');

        $reqStrings = array('name','title','table', 'engine','connection');
        $errors = array();
        $data = array();


        foreach ($reqStrings as $v)
        {
            $value = $this->request->post($v, 'string', '');

            if(!strlen($value))
                $errors[] = array('id'=>$v ,'msg'=>$this->lang->CANT_BE_EMPTY);

            if($v!=='name')
                $data[$v] = $value;
        }

        // check ACL Adapter
        if($useAcl && (empty($acl) || !class_exists($acl)))
            $errors[] = array('id'=>'acl' ,'msg'=>$this->lang->INVALID_VALUE);


        if(!empty($errors))
           $this->response->error($this->lang->FILL_FORM , $errors);

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
        $connectionManager = new Db_Manager($this->appConfig);
        $connection = $connectionManager->getDbConnection($data['connection']);
        $connectionCfg = $connectionManager->getDbConfig($data['connection']);

        //$db = Model::getGlobalDbConnection();
        $db = $connection;
        $tables = $db->listTables();
        $oConfigPath = Orm\Object\Config::getConfigPath();
        $configDir  = Config::storage()->getWrite() . $oConfigPath;

        $tableName = $data['table'];

        if($usePrefix){
            $tableName = $connectionCfg->get('prefix') . $tableName;
        }

        if(in_array($tableName, $tables ,true)){
           $this->response->error($this->lang->FILL_FORM , array(array('id'=>'table','msg'=>$this->lang->SB_UNIQUE)));
        }

        if(file_exists($configDir . strtolower($name).'.php')) {
           $this->response->error($this->lang->FILL_FORM, array(array('id' => 'name', 'msg' => $this->lang->SB_UNIQUE)));
        }

        if(!is_dir($configDir) && !@mkdir($configDir, 0655, true)){
           $this->response->error($this->lang->CANT_WRITE_FS.' '.$configDir);
        }

        /*
         * Write object config
         */
        if(!Config_File_Array::create($configDir. $name . '.php'))
           $this->response->error($this->lang->CANT_WRITE_FS . ' ' . $configDir . $name . '.php');

        $cfg = Config::storage()->get($oConfigPath. strtolower($name).'.php' , false , false);
        /*
         * Add fields config
         */
        $data['fields'] = array();

        $cfg->setData($data);
        $cfg->save();

        try{
            $cfg = Orm\Object\Config::factory($name);
            $cfg->setObjectTitle($data['title']);

            if(!$cfg->save())
               $this->response->error($this->lang->CANT_WRITE_FS);

            /*
             * Build database
            */
            $builder = new Orm\Object\Builder($name);
            $builder->build();

        }catch (Exception $e){
           $this->response->error($this->lang->CANT_EXEC . 'code 2');
        }
       $this->response->success();
    }

    protected function _updateObject($recordId , $name , array $data)
    {
        $dataDir = Config::storage()->getWrite() . $this->appConfig->get('object_configs');
        $objectConfigPath = $dataDir . $recordId.'.php';

        if(!is_writable($dataDir))
           $this->response->error($this->lang->get('CANT_WRITE_FS') . ' ' . $dataDir);

        if(file_exists($objectConfigPath) && !is_writable($objectConfigPath))
           $this->response->error($this->lang->get('CANT_WRITE_FS') . ' ' . $objectConfigPath);

        /*
         * Rename object
        */
        if($recordId!=$name)
        {
            $this->_renameObject($recordId, $name);
        }

        try {
            $config = Orm\Object\Config::factory($name);
        }catch (Exception $e){
           $this->response->error($this->lang->INVALID_VALUE);
        }

        $builder = new Orm\Object\Builder($name);

        /*
         * Rename Db Table
         */
        if($config->get('table')!==$data['table'])
        {
            if($builder->tableExists($data['table'] , true))
               $this->response->error($this->lang->FILL_FORM , array(array('id'=>'table','msg'=>$this->lang->SB_UNIQUE)));

            if(!$builder->renameTable($data['table']))
               $this->response->error($this->lang->CANT_RENAME_TABLE);
        }

        /*
         * Check and apply changes for DB Table engine
         */
        if($config->get('engine')!==$data['engine'])
        {
            $err = $builder->checkEngineCompatibility($data['engine']);

            if($err !== true)
               $this->response->error($this->lang->CANT_EXEC . ' ', $err);

            if(!$builder->changeTableEngine($data['engine']))
            {
                $errors = $builder->getErrors();
                if(!empty($errors))
                    $errors = implode(' <br>' , $errors);
               $this->response->error($this->lang->CANT_EXEC . ' ' . $errors);
            }
        }

        $data['fields'] = $config->getFieldsConfig(false);

        $config->setData($data);
        $config->setObjectTitle($data['title']);

        if(!$config->save())
           $this->response->error($this->lang->CANT_WRITE_FS);

       $this->response->success();
    }

    protected function _renameObject($oldName , $newName)
    {

        $newFileName = $this->appConfig->get('object_configs').$newName.'.php';
        //$oldFileName = $this->appConfig->get('object_configs').$oldName.'.php';

        if(file_exists($newFileName))
           $this->response->error($this->lang->FILL_FORM ,array(array('id'=>'name','msg'=>$this->lang->SB_UNIQUE)));

        $manager = new Backend_Orm_Manager();
        $renameResult = $manager->renameObject($this->appConfig['object_configs'] , $oldName , $newName);

        switch ($renameResult)
        {
            case 0:
                break;
            case Backend_Orm_Manager::ERROR_FS:
               $this->response->error($this->lang->CANT_WRITE_FS);
                break;
            case Backend_Orm_Manager::ERROR_FS_LOCALISATION:
               $this->response->error($this->lang->CANT_WRITE_FS . ' ('.$this->lang->LOCALIZATION_FILE.')');
                break;
            default:
               $this->response->error($this->lang->CANT_EXEC .' code 5');
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

        $name = $this->request->post('name', 'string', false);

        if(!$name)
           $this->response->error($this->lang->WRONG_REQUEST);

        $objectConfig = Orm\Object\Config::factory($name);

        // Check ACL permissions
        $acl = $objectConfig->getAcl();
        if($acl){
            if(!$acl->can(Orm\Object\Acl::ACCESS_CREATE , $name) || !$acl->can(Orm\Object\Acl::ACCESS_VIEW , $name)){
               $this->response->error($this->lang->get('ACL_ACCESS_DENIED'));
            }
        }

        try {
            $obj = Orm\Object::factory($name);
        } catch (Exception $e){
           $this->response->error($this->lang->get('CANT_GET_VALIDATE_INFO'));
        }

        $builder = new Orm\Object\Builder($name);
        $tableExists = $builder->tableExists();

        $colUpd = [];
        $indUpd = [];
        $keyUpd = [];

        if($tableExists){
            $colUpd =  $builder->prepareColumnUpdates();
            $indUpd =  $builder->prepareIndexUpdates();
            $keyUpd =  $builder->prepareKeysUpdate();
            $engineUpdate = $builder->prepareEngineUpdate();
        }

        $objects = $builder->getObjectsUpdatesInfo();

        if(empty($colUpd) && empty($indUpd) && empty($keyUpd) && $tableExists && !$engineUpdate && empty($objects))
           $this->response->success(array(),array('nothingToDo'=>true));

        $template = new Template();
        $template->disableCache();
        $template->engineUpdate = $engineUpdate;
        $template->columns = $colUpd;
        $template->indexes = $indUpd;
        $template->objects = $objects;
        $template->keys = $keyUpd;
        $template->tableExists = $tableExists;
        $template->tableName = $obj->getTable();
        $template->lang = $this->lang;

        $msg = $template->render(Application::getTemplatesPath() . 'orm_validate_msg.php');

        $this->response->success([],array('text'=>$msg,'nothingToDo'=>false));
    }

    /**
     * Build object action
     */
    public function buildAction()
    {
        $this->checkCanEdit();

        $name = $this->request->post('name', 'string', false);

        if(!$name)
           $this->response->error($this->lang->WRONG_REQUEST);

        if(!Orm\Object\Config::configExists($name))
           $this->response->error($this->lang->WRONG_REQUEST);

        $builder = new Orm\Object\Builder($name);

        if(!$builder->build() || !$builder->buildForeignKeys())
           $this->response->error($this->lang->CANT_EXEC.' ' . implode(',', $builder->getErrors()));

       $this->response->success();

    }

    /**
     * Build all objects action
     */
    public function buildAllAction()
    {
        $this->checkCanEdit();

        $names = $this->request->post('names', 'array', false);

        if(empty($names))
           $this->response->error($this->lang->get('WRONG_REQUEST'));

        $flag = false;

        /*
         * remove foreign keys
         */
        foreach ($names as $name)
        {
            try{
                $builder = new Orm\Object\Builder($name);
                if(!$builder->buildForeignKeys(true , false))
                    $flag = true;
            }catch(Exception $e){
                $flag = true;
            }
        }

        if(Orm\Object\Builder::foreignKeys())
        {
            /*
             * build only fields
             */
            foreach ($names as $name)
            {
                try{
                    $builder = new Orm\Object\Builder($name);
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
                    $builder = new Orm\Object\Builder($name);
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
                    $builder = new Orm\Object\Builder($name);
                    $builder->build();
                }catch(Exception $e){
                    $flag = true;
                }
            }
        }

        if ($flag)
           $this->response->error($this->lang->CANT_EXEC);
        else
           $this->response->success();
    }

    /**
     * Load Field config
     */
    public function loadFieldAction()
    {
        $object = $this->request->post('object', 'string',false);
        $field = $this->request->post('field', 'string',false);

        if(!$object || !$field)
           $this->response->error($this->lang->INVALID_VALUE);

        $manager = new Backend_Orm_Manager();
        $result = $manager->getFieldConfig($object , $field);

        if(!$result)
           $this->response->error($this->lang->INVALID_VALUE);

       $this->response->success($result);
    }

    /**
     * Save field configuration options
     */
    public function saveFieldAction()
    {
        $this->checkCanEdit();

        $manager = new Backend_Orm_Manager();

        $object = $this->request->post('objectName', 'string', false);
        $objectField = $this->request->post('objectField', 'string', false);
        $name = $this->request->post('name', 'string', false);

        if(!$object)
           $this->response->error($this->lang->WRONG_REQUEST);

        if(!$name)
           $this->response->error($this->lang->FILL_FORM , array(array('id'=>'name','msg'=>$this->lang->CANT_BE_EMPTY)));

        try{
            /**
             * @var Db_Object_Config
             */
            $objectCfg = Orm\Object\Config::factory($object);
        }catch (Exception $e){
           $this->response->error($this->lang->WRONG_REQUEST .' code 2');
        }

        $oFields = array_keys($objectCfg->getFieldsConfig());

        if($objectField !== $name && in_array($name, $oFields , true))
           $this->response->error($this->lang->FILL_FORM , array(array('id'=>'name','msg'=>$this->lang->SB_UNIQUE)));

        $unique = $this->request->post('unique', 'str', '');
        $newConfig = array();
        $newConfig['type'] = $this->request->post('type', 'str', '');
        $newConfig['title']= $this->request->post('title', 'str', '');
        $newConfig['unique'] = ($unique === false) ? '' : $unique;
        $newConfig['db_isNull'] = $this->request->post('db_isNull', 'boolean', false);
        $newConfig['required'] = $this->request->post('required', 'boolean', false);
        $newConfig['validator'] = $this->request->post('validator', 'string', '');

        if($newConfig['type']=='link')
        {
            if($newConfig['db_isNull'])
                $newConfig['required'] = false;
            /**
             * Process link field
             */
            $newConfig['link_config']['link_type'] = $this->request->post('link_type', 'str', 'object');

            if($newConfig['link_config']['link_type'] === Orm\Object\Config::LINK_DICTIONARY)
            {
                $newConfig['link_config']['object'] = $this->request->post('dictionary', 'str', '');
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
                $linkedObject = $this->request->post('object', 'string', false);
                if(!$linkedObject)
                   $this->response->error($this->lang->FILL_FORM , array(array('id'=>'object','msg'=>$this->lang->CANT_BE_EMPTY)));

                try {
                    $cf = Orm\Object\Config::factory($linkedObject);
                }catch(Exception $e){
                   $this->response->error($this->lang->FILL_FORM , array(array('id'=>'object','msg'=>$this->lang->INVALID_VALUE)));
                }
                $newConfig['link_config']['object'] = $linkedObject;

                switch ($newConfig['link_config']['link_type'])
                {
                    case Orm\Object\Config::LINK_OBJECT_LIST:

                        $newConfig['link_config']['relations_type'] = $this->request->post('relations_type' , 'string' , false);
                        if(!in_array($newConfig['link_config']['relations_type'] , array('polymorphic','many_to_many') , true)){
                            $newConfig['link_config']['relations_type'] = 'polymorphic';
                        }

                        $newConfig['db_type'] = 'longtext';
                        $newConfig['db_isNull'] = false;
                        $newConfig['db_default'] = '';
                        break;

                    case Orm\Object\Config::LINK_OBJECT:
                        $newConfig['db_isNull'] = (boolean) !$newConfig['required'];
                        $newConfig['db_type'] ='bigint';
                        $newConfig['db_default'] = false;
                        $newConfig['db_unsigned'] = true;
                        break;
                }
            }

        }elseif($newConfig['type']=='encrypted') {

            $setDefault = $this->request->post('set_default', 'boolean', false);

            if(!$setDefault){
                $newConfig['db_default'] = false;
            }else{
                $newConfig['db_default'] = $this->request->post('db_default', 'string', false);
            }

            $newConfig['db_type'] = 'longtext';
            $newConfig['is_search'] = false;
            $newConfig['allow_html'] = false;

        }else{
            $setDefault = $this->request->post('set_default', 'boolean', false);
            /*
             * Process std field
             */
            $newConfig['db_type'] = $this->request->post('db_type', 'str', 'false');
            if(!$newConfig['db_type'])
               $this->response->error($this->lang->FILL_FORM , array(array('id'=>'db_type','msg'=>$this->lang->CANT_BE_EMPTY)));

            if($newConfig['db_type']=='bool' || $newConfig['db_type']=='boolean'){
                /*
                 * boolean
                 */
                $newConfig['required'] = false;
                $newConfig['db_default'] = (integer)$this->request->post('db_default', 'bool', false);
            }elseif(in_array($newConfig['db_type'] , Orm\Object\Builder::$intTypes , true)){
                /*
                 * integer
                 */
                $newConfig['db_default'] = $this->request->post('db_default', 'integer', false);
                $newConfig['db_unsigned'] = $this->request->post('db_unsigned', 'bool', false);
            }elseif(in_array($newConfig['db_type'], Orm\Object\Builder::$floatTypes)){
                /*
                 * float
                 */
                $newConfig['db_default'] = $this->request->post('db_default', 'float', false);
                $newConfig['db_unsigned'] = $this->request->post('db_unsigned', 'bool', false);
                $newConfig['db_scale'] = $this->request->post('db_scale', 'integer', 0);
                $newConfig['db_precision'] = $this->request->post('db_precision', 'integer', 0);
            }elseif(in_array($newConfig['db_type'] , Orm\Object\Builder::$charTypes , true)){
                /*
                 * char
                 */
                $newConfig['db_default'] = $this->request->post('db_default', 'string', false);
                $newConfig['db_len'] = $this->request->post('db_len', 'integer', 255);
                $newConfig['is_search'] =$this->request->post('is_search', 'bool', false);
                $newConfig['allow_html'] =$this->request->post('allow_html', 'bool', false);
            }elseif(in_array($newConfig['db_type'] , Orm\Object\Builder::$textTypes , true)){
                /*
                 * text
                 */
                $newConfig['db_default'] = $this->request->post('db_default', 'string', false);
                $newConfig['is_search'] =  $this->request->post('is_search', 'bool', false);
                $newConfig['allow_html'] = $this->request->post('allow_html', 'bool', false);

                if(!$newConfig['required'])
                    $newConfig['db_isNull'] = true;

            }elseif(in_array($newConfig['db_type'] , Orm\Object\Builder::$dateTypes , true)){
                /*
                 * date
                 */
                if(!$newConfig['required'])
                    $newConfig['db_isNull'] = true;
            }
            else{
               $this->response->error($this->lang->FILL_FORM , array(array('id'=>'db_type','msg'=>$this->lang->INVALID_VALUE)));
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
                   $this->response->error($this->lang->CANT_EXEC);
                    break;
                case Backend_Orm_Manager::ERROR_FS_LOCALISATION:
                   $this->response->error($this->lang->CANT_WRITE_FS . ' ('.$this->lang->LOCALIZATION_FILE.')');
                    break;
            }

        } else{
            $objectCfg->setFieldConfig($name, $newConfig);
            $objectCfg->fixConfig();
        }
        if($objectCfg->save()){
            $builder = Orm\Object\Builder($object);
            $builder->build();
           $this->response->success();
        }else{
           $this->response->error($this->lang->CANT_WRITE_FS);
        }
    }

    /**
     * Remove Db_Object from system
     */
    public function removeObjectAction()
    {
        $this->_checkCanDelete();

        $objectName = $this->request->post('objectName', 'string', false);
        $deleteTable = $this->request->post('delete_table', Filter::FILTER_BOOLEAN, false);

        if(!$objectName)
           $this->response->error($this->lang->WRONG_REQUEST);

        try{
            $oConfig  = Orm\Object\Config::factory($objectName);
            if($deleteTable && ($oConfig->isLocked() || $oConfig->isReadOnly())){
               $this->response->error($this->lang->DB_CANT_DELETE_LOCKED_TABLE);
            }
        }catch (Exception $e){
           $this->response->error($this->lang->WRONG_REQUEST);
        }

        $manager = new Backend_Orm_Manager();
        $result = $manager->removeObject($objectName , $deleteTable);

        switch ($result){
            case 0 : $this->response->success();
                break;
            case Backend_Orm_Manager::ERROR_FS:
               $this->response->error($this->lang->CANT_WRITE_FS);
                break;
            case Backend_Orm_Manager::ERROR_DB:
               $this->response->error($this->lang->CANT_WRITE_DB);
                break;
            case Backend_Orm_Manager::ERROR_FS_LOCALISATION:
               $this->response->error($this->lang->CANT_WRITE_FS . ' ('.$this->lang->LOCALIZATION_FILE.')');
                break;
            case Backend_Orm_Manager::ERROR_HAS_LINKS:
               $this->response->error($this->lang->MSG_ORM_CAND_DELETE_LINKED);
                break;
            default:
               $this->response->error($this->lang->CANT_EXEC);
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
        $manager = new Orm\Object\Manager();
        $names = $manager->getRegisteredObjects();
        $showObj = $this->request->post('objects','array',[]);

        if(empty($showObj)){
            foreach($names as $name)
                if(!isset($items[$name]['show']) || $items[$name]['show'])
                    $showObj[] = $name;
        }else{
            foreach ($showObj as $k => $name)
                if(!in_array($name, $names, true))
                    unset($showObj[$k]);
        }

        $defaultX = 10;
        $defaultY = 10;

        foreach($names as $index=>$objectName)
        {
            $objectConfig = Orm\Object\Config::factory($objectName);
            if(!empty($objectConfig->isRelationsObject()) || !in_array($objectName,$showObj)){
                unset($names[$index]);
                continue;
            }
            
            $data[$objectName]['links'] = $objectConfig->getLinks();

            $objectConfig = Orm\Object\Config::factory($objectName);
            $fields = $objectConfig->getFieldsConfig();

            foreach($fields as $fieldName => $fieldData)
            {
                $data[$objectName]['fields'][] = $fieldName;

                if(isset($items[$objectName])){
                    $data[$objectName]['position'] = array('x'=>$items[$objectName]['x'],'y'=>$items[$objectName]['y']);
                    $data[$objectName]['savedlinks'] = [];
                    if(!empty(isset($items[$objectName]['links'])))
                        $data[$objectName]['savedlinks'] = $items[$objectName]['links'];
                }else{
                    $data[$objectName]['position'] = array('x'=>$defaultX , 'y'=>$defaultY);
                    $defaultX+=10;
                    $defaultY+=10;
                }
            }
            sort($data[$objectName]['fields']);
        }

        foreach($names as $objectName)
        {
            foreach($data[$objectName]['links'] as $link => $link_value)
            {
                if(!isset($data[$link]))
                    continue;

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

       $this->response->success($result);
    }

    /**
     * Save object coordinates
     */
    public function saveUmlMapAction()
    {
        $this->checkCanEdit();

        $map = $this->request->post('map', 'raw', '');

        if(!strlen($map))
           $this->response->error($this->lang->WRONG_REQUEST);

        $data = json_decode($map , true);

        $config = Config::storage()->get(self::UML_MAP_CFG,true, false);
        $saved = $config->get('items');
        $manager = new Orm\Object\Manager();
        $registered = $manager->getRegisteredObjects();

        /**
         * Check objects map from request and set show property
         */
        foreach($data as $k => $item){
            if(!in_array($k,$registered,true)){
                unset($data[$k]);
                continue;
            }
            $data[$k]['show'] = true;
        }

        /**
         * Add saved map objects with checking that object is registered
         */
        foreach($saved as $k => $item){
            $item['show'] = false;
            if(!array_key_exists($k,$data) && in_array($k,$registered,true))
                $data[$k] = $item;
        }

        $config->set('items' , $data);

        if($config->save())
           $this->response->success();
        else
           $this->response->error($this->lang->CANT_WRITE_FS);
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
        $manager = new Backend_Orm_Connections_Manager($this->appConfig->get('db_configs'));
        $list = $manager->getConnections(0);
        $data = array();
        if(!empty($list))
        {
            foreach($list as $k=>$v)
            {
                $data[] = array('id'=> $k);
            }
        }
       $this->response->success($data);
    }

    /**
     * Get connection types (prod , dev , test ... etc)
     */
    public function connectionTypesAction()
    {
        $data = array();
        foreach ($this->appConfig->get('db_configs') as $k=>$v){
            $data[]= array('id'=>$k , 'title'=>$this->lang->get($v['title']));
        }
       $this->response->success($data);
    }

    /*
     * Get list of field validators
     */
    public function listValidatorsAction()
    {
        $validators = [];
        $files = File::scanFiles('./dvelum/library/Validator', array('.php'), false, File::Files_Only);

        foreach ($files as $v)
        {
            $name = substr(basename($v), 0, -4);
            if($name != 'Interface')
                $validators[] = array('id'=>'Validator_'.$name, 'title'=>$name);
        }

       $this->response->success($validators);
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
            'js/app/system/orm/taskStatusWindow.js',
            'js/app/system/orm/selectObjectsWindow.js'
        );

        if(!$this->appConfig->get('development')){
            die('Use development mode');
        }

        $s = '';
        $totalSize = 0;

        $wwwPath = $this->appConfig->get('wwwpath');
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
       $this->response->success($list);
    }

    /**
     * Check and fix config files
     */
    public function fixConfigsAction()
    {
        $manager = new Orm\Object\Manager();
        $names = $manager->getRegisteredObjects();
        foreach ($names as $objectName)
        {
            $cfg = Orm\Object\Config::factory($objectName);
            $cfg->save();
        }
       $this->response->success();
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
     * Check background process status
     */
    public function taskStatAction()
    {
        $object = $this->request->post('object' , 'string' , false);
        $type = $this->request->post('type' , 'string' , false);

        if(!$object || ! $type)
           $this->response->error();

        switch($type){
            case 'encrypt':
                $container = $this->encryptContainerPrefix . $object;
                break;
            case 'decrypt':
                $container = $this->decryptContainerPrefix . $object;
                break;
            default:$this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $session = Store_Session::getInstance();

        if(!$session->keyExists($container)){
           $this->response->error();
        }

        $pid = $session->get($container);
        $taskModel = Model::factory('bgtask');
        $statusData = $taskModel->getItem($pid);

        if(empty($statusData))
           $this->response->error($this->lang->get('CANT_EXEC'));

       $this->response->success(array(
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

        foreach ($this->appConfig->get('db_configs') as $k=>$v){
            $dbConfigs[]= array('id'=>$k , 'title'=>$this->lang->get($v['title']));
        }

        //tooltips
        $lPath = $this->appConfig->get('language').'/orm.php';
        Lang::addDictionaryLoader('orm_tooltips', $lPath, Config::File_Array);

        $projectData['includes']['js'][] = $this->resource->cacheJs('
           var useForeignKeys = '.((integer) $this->appConfig['foreign_keys']).';
           var dbConfigsList = '.json_encode($dbConfigs).';
           var ormTooltips = '.Lang::lang('orm_tooltips')->getJson().';
        ');

        $projectData['includes']['css'][] = '/css/system/joint.min.css';
        $projectData['includes']['js'][] = '/js/lib/uml/lodash.min.js';
        $projectData['includes']['js'][] = '/js/lib/uml/backbone-min.js';
        $projectData['includes']['js'][] = '/js/lib/uml/joint.min.js';
        $projectData['includes']['js'][] = '/js/app/system/ORM.js?v='.$version;

        /*
         * Module bootstrap
         */
        if(file_exists($this->appConfig->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}
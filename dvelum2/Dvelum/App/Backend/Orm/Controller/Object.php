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

use Dvelum\App\Backend\Orm\Manager;
use Dvelum\Config;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Lang;
use Dvelum\View;
use Dvelum\Template;

class Object extends \Dvelum\App\Backend\Controller
{
    public function getModule()
    {
        return 'Orm';
    }

    public function indexAction(){}

    /**
     * Validate Object Db Structure
     */
    public function validateAction()
    {
        $engineUpdate = false;

        $name = $this->request->post('name', 'string', false);

        if(!$name)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

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
        } catch (\Exception $e){
            $this->response->error($this->lang->get('CANT_GET_VALIDATE_INFO'));
        }

        $builder = Orm\Object\Builder::factory($name);
        $tableExists = $builder->tableExists();

        $colUpd = [];
        $indUpd = [];
        $keyUpd = [];

        if($tableExists){
            $colUpd =  $builder->prepareColumnUpdates();
            $indUpd =  $builder->prepareIndexUpdates();
            $keyUpd =  $builder->prepareKeysUpdate();

            if(method_exists($builder,'prepareEngineUpdate')){
                $engineUpdate = $builder->prepareEngineUpdate();
            }
        }

        $objects = $builder->getRelationUpdates();

        if(empty($colUpd) && empty($indUpd) && empty($keyUpd) && $tableExists && !$engineUpdate && empty($objects)){
            $this->response->success([],['nothingToDo'=>true]);
        }

        $template = new \Dvelum\View();
        $template->disableCache();
        $template->engineUpdate = $engineUpdate;
        $template->columns = $colUpd;
        $template->indexes = $indUpd;
        $template->objects = $objects;
        $template->keys = $keyUpd;
        $template->tableExists = $tableExists;
        $template->tableName = $obj->getTable();
        $template->lang = $this->lang;

        $msg = $template->render(\Dvelum\App\Application::getTemplatesPath() . 'orm_validate_msg.php');

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
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        if(!Orm\Object\Config::configExists($name))
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        $builder = Orm\Object\Builder::factory($name);

        if(!$builder->build() || !$builder->buildForeignKeys())
            $this->response->error($this->lang->get('CANT_EXEC').' ' . implode(',', $builder->getErrors()));

        $this->response->success();
    }
    /**
     * Get object fields
     */
    public function fieldsAction()
    {
        $object = $this->request->post('object', 'string', false);

        if(!$object)
            $this->response->error($this->lang->get('INVALID_VALUE'));

        try{
            $objectConfig = Orm\Object\Config::factory($object);
        }catch (\Exception $e){
            $this->response->error($this->lang->get('INVALID_VALUE'));
        }

        $builder = Orm\Object\Builder::factory($object);
        $brokenFields = $builder->hasBrokenLinks();

        $fieldsCfg = $objectConfig->getFieldsConfig();

        foreach ($fieldsCfg as $k=>&$v)
        {
            $v['name'] = $k;
            $v['unique'] = $objectConfig->getField($k)->isUnique();

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

            if(in_array($v['db_type'], Orm\Object\Builder::$charTypes , true)){
                $v['type'].=' ('.$v['db_len'].')';
            }elseif (in_array($v['db_type'], Orm\Object\Builder::$floatTypes , true)){
                $v['type'].=' ('.$v['db_scale'].','.$v['db_precision'].')';
            }
        }unset($v);
        $this->response->json(array_values($fieldsCfg));
    }
    /**
     * Get object indexes
     */
    public function indexesAction()
    {
        $object = $this->request->post('object', 'string', false);

        if(!$object)
            $this->response->error($this->lang->get('INVALID_VALUE'));

        try{
            $objectConfig = Orm\Object\Config::factory($object);
        }catch (\Exception $e){
            $this->response->error($this->lang->get('INVALID_VALUE'));
        }

        $indexCfg = $objectConfig->getIndexesConfig();

        foreach ($indexCfg as $k=>&$v){
            $v['columns'] = implode(', ', $v['columns']);
            $v['name'] = $k;
        }unset($v);

        $this->response->json(array_values($indexCfg));
    }

    /**
     * Remove Db_Object from system
     */
    public function removeAction()
    {
        $this->checkCanDelete();

        $objectName = $this->request->post('objectName', 'string', false);
        $deleteTable = $this->request->post('delete_table', \Filter::FILTER_BOOLEAN, false);

        if(!$objectName)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        try{
            $oConfig  = Orm\Object\Config::factory($objectName);
            if($deleteTable && ($oConfig->isLocked() || $oConfig->isReadOnly())){
                $this->response->error($this->lang->get('DB_CANT_DELETE_LOCKED_TABLE'));
            }
        }catch (\Exception $e){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $manager = new Manager();

        $result = $manager->removeObject($objectName , $deleteTable);

        switch ($result){
            case 0 : $this->response->success();
                break;
            case Manager::ERROR_FS:
                $this->response->error($this->lang->get('CANT_WRITE_FS'));
                break;
            case Manager::ERROR_DB:
                $this->response->error($this->lang->get('CANT_WRITE_DB'));
                break;
            case Manager::ERROR_FS_LOCALISATION:
                $this->response->error($this->lang->get('CANT_WRITE_FS') . ' ('.$this->lang->get('LOCALIZATION_FILE').')');
                break;
            case Manager::ERROR_HAS_LINKS:
                $this->response->error($this->lang->get('MSG_ORM_CAND_DELETE_LINKED'));
                break;
            default:
                $this->response->error($this->lang->get('CANT_EXEC'));
        }
    }

    /**
     * Load Db Object info
     */
    public function loadAction()
    {
        $object = $this->request->post('object', 'string',false);

        if($object === false)
            $this->response->error($this->lang->get('INVALID_VALUE'));

        try {
            $config = Orm\Object\Config::factory($object);
            $info = $config->__toArray();
            $info['name'] = $object;
            $info['use_acl'] = false;

            if($info['acl'])
                $info['use_acl'] = true;

            unset($info['fields']);
            $this->response->success($info);
        }catch (\Exception $e){
            $this->response->error($this->lang->get('INVALID_VALUE'));
        }
    }
    
    /*
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
                $errors[] = array('id'=>$v ,'msg'=>$this->lang->get('CANT_BE_EMPTY'));

            if($v!=='name')
                $data[$v] = $value;
        }

        // check ACL Adapter
        if($useAcl && (empty($acl) || !class_exists($acl)))
            $errors[] = array('id'=>'acl' ,'msg'=>$this->lang->get('INVALID_VALUE'));


        if(!empty($errors))
            $this->response->error($this->lang->get('FILL_FORM') , $errors);

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
            $this->createObject($name , $data);
        }else{
            $this->updateObject($recordId, $name, $data);
        }
    }

    /**
     * Create Db_Object
     * @param string $name - object name
     * @param array $data - object config
     */
    protected function createObject($name , array $data)
    {
        $usePrefix = $data['use_db_prefix'];
        $connectionManager = new \Dvelum\Db\Manager($this->appConfig);
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
            $this->response->error($this->lang->get('FILL_FORM') , array(array('id'=>'table','msg'=>$this->lang->get('SB_UNIQUE'))));
        }

        if(file_exists($configDir . strtolower($name).'.php')) {
            $this->response->error($this->lang->get('FILL_FORM'), array(array('id' => 'name', 'msg' => $this->lang->get('SB_UNIQUE'))));
        }

        if(!is_dir($configDir) && !@mkdir($configDir, 0655, true)){
            $this->response->error($this->lang->get('CANT_WRITE_FS').' '.$configDir);
        }

        /*
         * Write object config
         */
        if(!Config\File\AsArray::create($configDir. $name . '.php'))
            $this->response->error($this->lang->get('CANT_WRITE_FS') . ' ' . $configDir . $name . '.php');

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
                $this->response->error($this->lang->get('CANT_WRITE_FS'));

            /*
             * Build database
            */
            $builder = Orm\Object\Builder::factory($name);
            $builder->build();

        }catch (Exception $e){
            $this->response->error($this->lang->get('CANT_EXEC') . 'code 2');
        }
        $this->response->success();
    }

    protected function updateObject($recordId , $name , array $data)
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
            $this->renameObject($recordId, $name);
        }

        try {
            $config = Orm\Object\Config::factory($name);
        }catch (\Exception $e){
            $this->response->error($this->lang->get('INVALID_VALUE'));
        }

        $builder = Orm\Object\Builder::factory($name);

        /*
         * Rename Db Table
         */
        if($config->get('table')!==$data['table'])
        {
            if($builder->tableExists($data['table'] , true))
                $this->response->error($this->lang->get('FILL_FORM') , array(array('id'=>'table','msg'=>$this->lang->get('SB_UNIQUE'))));

            if(!$builder->renameTable($data['table']))
                $this->response->error($this->lang->get('CANT_RENAME_TABLE'));
        }

        /*
         * Check and apply changes for DB Table engine
         */
        if($config->get('engine')!==$data['engine'])
        {
            $err = $builder->checkEngineCompatibility($data['engine']);

            if($err !== true)
                $this->response->error($this->lang->get('CANT_EXEC') . ' ', $err);

            if(!$builder->changeTableEngine($data['engine']))
            {
                $errors = $builder->getErrors();
                if(!empty($errors))
                    $errors = implode(' <br>' , $errors);
                $this->response->error($this->lang->get('CANT_EXEC') . ' ' . $errors);
            }
        }

        $data['fields'] = $config->getFieldsConfig(false);

        $config->setData($data);
        $config->setObjectTitle($data['title']);

        if(!$config->save())
            $this->response->error($this->lang->get('CANT_WRITE_FS'));

        $this->response->success();
    }

    protected function renameObject($oldName , $newName)
    {

        $newFileName = $this->appConfig->get('object_configs').$newName.'.php';
        //$oldFileName = $this->appConfig->get('object_configs').$oldName.'.php';

        if(file_exists($newFileName))
            $this->response->error($this->lang->get('FILL_FORM') ,array(array('id'=>'name','msg'=>$this->lang->get('SB_UNIQUE'))));

        $manager = new Backend_Orm_Manager();
        $renameResult = $manager->renameObject($this->appConfig['object_configs'] , $oldName , $newName);

        switch ($renameResult)
        {
            case 0:
                break;
            case Backend_Orm_Manager::ERROR_FS:
                $this->response->error($this->lang->get('CANT_WRITE_FS'));
                break;
            case Backend_Orm_Manager::ERROR_FS_LOCALISATION:
                $this->response->error($this->lang->get('CANT_WRITE_FS') . ' ('.$this->lang->LOCALIZATION_FILE.')');
                break;
            default:
                $this->response->error($this->lang->get('CANT_EXEC') .' code 5');
        }
        /*
         * Clear cache
         */
        Config::resetCache();
    }
}
<?php
class Backend_Orm_Connections_Controller extends Backend_Controller
{
    /**
     * @var Backend_Orm_Connections_Manager
     */
    protected $_manager;
    
    public function getModule()
    {
        return 'Orm';
    }
    
    public function __construct(){
        parent::__construct();
        $this->_manager = new Backend_Orm_Connections_Manager($this->_configMain->get('db_configs'));
    }
    
    public function indexAction(){
        exit();
    }

    public function listAction()
    {
        $devType = Request::post('devType', 'int', false);
        
        if($devType === false || !$this->_manager->typeExists($devType))
            Response::jsonError($this->_lang->WRONG_REQUEST .' undefined devType');
        
        $connections = $this->_manager->getConnections($devType);
        $data = array();
        if(!empty($connections))
        {
            foreach ($connections as $name=>$cfg)
            {
                if($name === 'default')
                    $system = true;
                else
                    $system = false;
                
               $data[] = array(
                    'id' => $name , 
                    'system' => $system, 
                    'devType' => $devType,
                    'username' => $cfg->get('username'),
                    'dbname' => $cfg->get('dbname'),
                    'host' => $cfg->get('host'),
                    'adapter'=> $cfg->get('adapter')
               );
            }
        }
        Response::jsonSuccess($data);
    } 
    
    public function removeAction()
    {
        $id = Request::post('id', 'string', false);
        
        if($id === false)
            Response::jsonError($this->_lang->WRONG_REQUEST .' undefined id');
        
        try{
            $this->_manager->removeConnection($id);
        }
        catch (Exception $e){
            Response::jsonError($e->getMessage());
        }
        
        Response::jsonSuccess();
    }
    
    public function loadAction()
    {
        $id = Request::post('id', 'string', false);
        $devType = Request::post('devType', 'int', false);
        
        if($id === false)
            Response::jsonError($this->_lang->WRONG_REQUEST .' undefined id');
        
        if($devType === false || !$this->_manager->typeExists($devType))
            Response::jsonError($this->_lang->WRONG_REQUEST .' undefined devType');
        
        
        $data = $this->_manager->getConnection($devType , $id);

        if(!$data)
            Response::jsonError($this->_lang->CANT_LOAD);

        $data = $data->__toArray();
        $data['id'] = $id;
        unset($data['password']);
        Response::jsonSuccess($data);       
    } 
    
    public function saveAction()
    {
        $oldId = Request::post('oldid', 'string', false);
        $id = Request::post('id', 'string', false);
        $devType = Request::post('devType', 'int', false);
        $host = Request::post('host', 'string', false);
        $user = Request::post('username', 'string', false);
        $base = Request::post('dbname', 'string', false);
        $charset = Request::post('charset', 'string', false);
        $pass = Request::post('password', 'string', false);

        $setpass = Request::post('setpass', 'boolean', false);
        $adapter = Request::post('adapter', 'string', false);
        $adapterNamespace = Request::post('adapterNamespace', 'string', false);
        $port = Request::post('port', 'int', false);
        $prefix = Request::post('prefix', 'string', '');
        
        if($devType === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        /*
         * INPUT FIX
         */
        if($oldId === 'false')
            $oldId = false;
        
        if($oldId === false || empty($oldId))
        {
            $cfg = $this->_manager->getConfig();
            foreach ($cfg as $type=>$data)
                if($this->_manager->connectionExists($type , $id))
                    Response::jsonError($this->_lang->FILL_FORM , array('id'=>$this->_lang->SB_UNIQUE));
            
            if(!$this->_manager->createConnection($id))
                Response::jsonError($this->_lang->CANT_CREATE);
                
            $con = $this->_manager->getConnection($devType, $id);    
        }
        else
        {   
            if($oldId!==$id)
            {
                $cfg = $this->_manager->getConfig();
                foreach ($cfg as $type=>$data)
                    if($this->_manager->connectionExists($type , $id))
                        Response::jsonError($this->_lang->FILL_FORM , array('id'=>$this->_lang->SB_UNIQUE));
            }
            
            if(!$this->_manager->connectionExists($devType, $id) && $oldId===$id)
                Response::jsonError($this->_lang->WRONG_REQUEST);
            
            $con = $this->_manager->getConnection($devType, $oldId);
        } 

        if(!$con)
            Response::jsonError($this->_lang->CANT_CREATE);
        
        if($setpass)
            $con->set('password', $pass);
                
        if($port!==false && $port!==0){
            $con->set('port', $port);
        }else{
            $con->remove('port');
        }
        
        $con->set('username', $user);
        $con->set('dbname', $base);
        $con->set('host', $host);
        $con->set('charset', $charset);
        $con->set('adapter', $adapter);
        $con->set('adapterNamespace', $adapterNamespace);
        $con->set('prefix' , $prefix);
        
        if(!$con->save())
            Response::jsonError($this->_lang->CANT_WRITE_FS . ' ' . $con->getName());
        
        if($oldId !==false && $oldId!==$id){
            if(!$this->_manager->renameConnection($oldId, $id))
                 Response::jsonError($this->_lang->CANT_WRITE_FS);
        }      
        Response::jsonSuccess();      
    } 
    
    public function testAction()
    {
        $id = Request::post('id', 'string', false);
        $devType = Request::post('devType', 'int', false);
        $port = Request::post('port', 'int', false);
        $host = Request::post('host', 'string', false);
        $user = Request::post('username', 'string', false);
        $base = Request::post('dbname', 'string', false);
        $charset = Request::post('charset', 'string', false);
        $pass = Request::post('password', 'string', false);
        $setpass = Request::post('setpass', 'boolean', false);
        $adapter = Request::post('adapter', 'string', false);    
        $adapterNamespace = Request::post('adapterNamespace', 'string', false);
        
        if($devType === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        $config = array(
                'username' => $user,
                'password' => $pass,
                'dbname' => $base,
                'host' => $host,
                'charset' => $charset,
                'adapter' => $adapter,
                'adapterNamespace' => $adapterNamespace
        );
        
        if($port!==false)
            $config['port'] = $port;
        
        if($id!==false && $id!=='false' && !$setpass)
        {
            $oldCfg = $this->_manager->getConnection($devType, $id);
            if(!$oldCfg)
                Response::jsonError($this->_lang->WRONG_REQUEST .' invalid file');
            	
            $config['password']	 = $oldCfg->get('password');
        }
        
        try{
            $db = Zend_Db::factory($adapter , $config);
            $db->query('SET NAMES ' . $charset);
            Response::jsonSuccess();
        }catch (Exception $e){
            Response::jsonError($this->_lang->CANT_CONNECT.' '.$e->getMessage());
        }
    }
    
    public function tablelistAction()
    {
        $connectionId = Request::post('connId', 'string', false);
        $connectionType = Request::post('type', 'integer', false);
         
        if($connectionId === false || $connectionType===false)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $cfg = $this->_manager->getConnection($connectionType, $connectionId);
        if(!$cfg)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        $cfg = $cfg->__toArray();
        
         try{
            $db = Zend_Db::factory($cfg['adapter'] , $cfg);
            $db->query('SET NAMES ' . $cfg['charset']);
        }catch (Exception $e){
            Response::jsonError($this->_lang->CANT_CONNECT.' '.$e->getMessage());
        }
        
        
        $data = array();
        try{
            $list = $db->listTables();
        }catch (Exception $e){
            Response::jsonError($e->getMessage());
        }

         
        foreach($list as $v)
            $data[] = array('id'=>$v,'title'=>$v);
    
        Response::jsonSuccess($data);
    }
    
    public function fieldslistAction()
    {
        $connectionId = Request::post('connId', 'string', false);
        $connectionType = Request::post('type', 'integer', false);
        $table = Request::post('table','string',false);
         
        if($connectionId === false || $connectionType===false || $table===false)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        $cfg = $this->_manager->getConnection($connectionType, $connectionId);

        if(!$cfg)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        	
        $cfg = $cfg->__toArray();       
        try{
            $db = Zend_Db::factory($cfg['adapter'] , $cfg);
            $db->query('SET NAMES ' . $cfg['charset']);
        }catch (Exception $e){
            Response::jsonError($this->_lang->CANT_CONNECT.' '.$e->getMessage());
        }
    
        $data = array();
        $desc = $db->describeTable($table);
    
        foreach ($desc as $v=>$k)
            $data[] = array('name'=>$v, 'type'=>$k['DATA_TYPE']);
    
        Response::jsonSuccess($data);
    }
    
    public function externaltablesAction()
    {
        $connectionId = Request::post('connId', 'string', false);
        $connectionType = Request::post('type', 'integer', false);
        
        if($connectionId === false || $connectionType===false)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        $cfg = $this->_manager->getConnection($connectionType, $connectionId);
        
        if(!$cfg)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        $cfg = $cfg->__toArray();
        try{
            $db = Zend_Db::factory($cfg['adapter'] , $cfg);
            $db->query('SET NAMES ' . $cfg['charset']);
            $tables = $db->listTables();
        }catch (Exception $e){
            Response::jsonError($this->_lang->CANT_CONNECT.' '.$e->getMessage());
        }
        
        $data = array();

       $manager = new Db_Object_Manager();
       $objects = $manager->getRegisteredObjects();
       
       $tablesObjects = array();
       
       foreach ($objects as $object)
       {
           $model = Model::factory($object);
           $tablesObjects[$model->table()][] = $object;
       }
       
       if(!empty($tables))
       {
            foreach ($tables as $table)
            {
                $same = false;

                if(isset($tablesObjects[$table]) && !empty($tablesObjects[$table]))
                {
                    foreach ($tablesObjects[$table] as $oName)
                    {
                        $mCfg = Model::factory($oName)->getDbConnection()->getConfig();
                        if($mCfg['host'] === $cfg['host'] && $mCfg['dbname'] === $cfg['dbname'])
                        {
                            $same = true;
                            break;
                        }
                    }    
                }
                if(!$same)
                    $data[] = array('name' => $table);
            }
        }        
        Response::jsonSuccess($data);
    }
    
    public function connectobjectAction()
    {
        $connectionId = Request::post('connId', 'string', false);
        $connectionType = Request::post('type', 'integer', false);
        $table = Request::post('table', 'string' , false);
        
        if($connectionId === false || $connectionType===false || $table === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        $cfg = $this->_manager->getConnection($connectionType, $connectionId);
        
        if(!$cfg)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        $cfg = $cfg->__toArray();
        try{
            $db = Zend_Db::factory($cfg['adapter'] , $cfg);
            $db->query('SET NAMES ' . $cfg['charset']);
            $tables = $db->listTables();
        }catch (Exception $e){
            Response::jsonError($this->_lang->CANT_CONNECT.' '.$e->getMessage());
        }

        $import = new Db_Object_Import();
        
        if(!$import->isValidPrimaryKey($db , $table))
        {
            $errors = $import->getErrors();
            
            if(!empty($errors))
                $errors = '<br>' . implode('<br>', $errors);
            else
                $errors = '';
            
            Response::jsonError( $this->_lang->DB_CANT_CONNECT_TABLE. ' '. $this->_lang->DB_MSG_UNIQUE_PRIMARY. ' ' . $errors);
        }
        
        $manager = new Db_Object_Manager();
        $newObjectName = strtolower(str_replace('_', '', $table));
        
        if($manager->objectExists($newObjectName))
        {
            $newObjectName =  strtolower(str_replace('_', '', $cfg['dbname'])).$newObjectName;
            if($manager->objectExists($newObjectName))
            {
                $k=0;
                $alphabet = Utils_String::alphabetEn();
                
                while ($manager->objectExists($newObjectName)){
                    if(!isset($alphabet[$k]))
                        Response::jsonError('Can not create unique object name' . $errors);                         
                    $newObjectName.= $alphabet[$k];
                    $k++;
                }
            }
        }
       
        $config = $import->createConfigByTable($db, $table , $cfg['prefix']);
        $config['connection'] = $connectionId;
        
        if(!$config)
        {
            $errors = $import->getErrors();
            
            if(!empty($errors))
                $errors = '<br>' . implode('<br>', $errors);
            else
                $errors = '';
            
            Response::jsonError($this->_lang->DB_CANT_CONNECT_TABLE.' ' . $errors);           
        }else{
            $path = $this->_configMain->get('object_configs').$newObjectName.'.php';

            if(!Config::storage()->create($path)){
                Response::jsonError($this->_lang->CANT_WRITE_FS .' '. $path);
            }

            $cfg = Config::storage()->get($path,true,true);
            $cfg->setData($config);
            if(!$cfg->save()){
                Response::jsonError($this->_lang->CANT_WRITE_FS .' '. $path);
            }
        }
        
        Response::jsonSuccess();
    }
}
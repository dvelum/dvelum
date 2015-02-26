<?php
class Backend_Orm_Connections_Manager
{
    protected $_config;
    
    public function __construct(array $config)
    {
        $this->_config = $config;
    }
    
    public function typeExists($devType)
    {
        return isset($this->_config[$devType]);
    }
    /**
     * Get connections list
     * @param integer $devType
     * @throws Exception
     * @return array
     */
    public function getConnections($devType)
    {
        if(!$this->typeExists($devType))
            throw new Exception('Backend_Orm_Connections_Manager :: getConnections undefined dev type ' . $devType);
        
        $files = File::scanFiles($this->_config[$devType]['dir'] , array('.php') , false , File::Files_Only);
        $result = array();
        
        if(!empty($files))
            foreach ($files as $item)
                $result[substr(basename($item),0,-4)] = Config::factory(Config::File_Array, $item);
            
        return $result;
    }
        
    /**
     * Remove DB Connection config
     * Caution! Connection settings will be removed for all system modes.
     * @param string $id
     * @throws Exception
     */
    public function removeConnection($id)
    {
        $errors = array();
        /*
         * Check for write permissions before operation
         */
        foreach ($this->_config as $devType =>$data)
        {
            $file = $data['dir'] . $id .'.php';
            if(file_exists($file) && !is_writable($file))
                $errors[] = $file;
        }
        
        if(!empty($errors))
            throw new Exception(Lang::lang()->get('CANT_WRITE_FS') . ' ' . implode(', ',$errors));
             
        foreach ($this->_config as $devType=>$data)
        {
            $file = $data['dir'] . $id .'.php';
            if(!@unlink($file)){
                throw new Exception(Lang::lang()->get('CANT_WRITE_FS') . ' ' . $file);
            }
        }
    }
    /**
     * Get connection config
     * @param integer $devType
     * @param string $id
     * @return boolean|Config_Abstract
     */
    public function getConnection($devType , $id)
    {
        if(!$this->typeExists($devType))
            return false;
           
        if(!file_exists($this->_config[$devType]['dir'] . $id . '.php'))
            return false;
        
        return Config::factory(Config::File_Array, $this->_config[$devType]['dir'] . $id . '.php');
    }
    
    public function createConnection($id)
    {       
        foreach ($this->_config as $devType=>$data)
            if($this->connectionExists($devType , $id))
                return false;
        
        foreach ($this->_config as $devType=>$data)
        {
            if(!Config_File_Array::create($this->_config[$devType]['dir'] . $id . '.php'))
                return false;
            
            $c = $this->getConnection($devType, $id);
            $c->setData(array(
                    'username' => '',
                    'password' => '',
                    'dbname'   => '',
                    'host'     => '',
                    'charset'  => 'UTF8',
                    'prefix'   => '',
                    'adapter'  => 'Mysqli',
                    'adapterNamespace' => 'Db_Adapter'
            )); 
            if(!$c->save())
                return false;
        }
        return true;       
    }
    /**
     * Rename DB connection config
     * @param string $oldId
     * @param string $newId
     * @return boolean
     */
    public function renameConnection($oldId , $newId)
    {
        /**
         * Check permissions
         */
        foreach ($this->_config as $devType=>$data)
        {
            if(!is_writable($data['dir']) 
               || $this->connectionExists($devType, $newId) 
               || !file_exists($data['dir'] . $oldId . '.php')
               || !is_writable($data['dir'] . $oldId . '.php')
            ){
                return false;
            }
        }       
        foreach ($this->_config as $devType=>$data){
            rename($this->_config[$devType]['dir'] . $oldId . '.php', $this->_config[$devType]['dir'] . $newId . '.php');
        }       
        return true;
    }
    /**
     * Check if DB Connection exists
     * @param integer $devType
     * @param string $id
     * @return boolean
     */
    public function connectionExists($devType , $id)
    {
        if(!$this->typeExists($devType))
            return false;
        
        return file_exists($this->_config[$devType]['dir'] . $id . '.php');
    }
    /**
     * Get connections config
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }
}
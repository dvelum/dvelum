<?php
/**
 * Import component, experimental class
 * @package Db
 * @subpackage Db_Object 
 * @license General Public License version 3
 * @example
 */
class Db_Object_Import
{
    protected $_errors = array();
    
    public function getErrors()
    {
        return $this->_errors;
    }
    
    /**
     * Find PRIMARY KEY
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $table
     * @return array | boolean (false)
     */
    public function findPrimaryKey(Zend_Db_Adapter_Abstract $db , $table)
    {
        $fields = $db->describeTable($table);
        $primary = false;
        foreach ($fields as $name=>$info){
            if($info['PRIMARY']==1){
                $primary = $info;
                break;
            }
        }               
        return $primary;
    }
      
    /**
     * Check if PRIMARY KEY of external DB table is correct
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $table
     * @return boolean
     */
    public function isValidPrimaryKey(Zend_Db_Adapter_Abstract $db , $table)
    {     
        $primary = $this->findPrimaryKey($db, $table);

        if(!$primary){
            $this->_errors[] = 'No primary key';
            return false;
        } 
               
        $dataType = strtolower($primary['DATA_TYPE']);
        
        if(!in_array($dataType , Db_Object_Builder::$numTypes , true)){
            $this->_errors[] = 'PRIMARY KEY is not numeric';
            return false;
        }
        
        if($primary['IDENTITY']!=1){
            $this->_errors[] = 'The PRIMARY KEY is not using auto-increment';
            return false;
        }     
        return true;
    }

    /**
     * @todo cleanup the code
     * @param Zend_Db_Adapter_Mysqli $dbAdapter
     * @param string $tableName
     * @throws Exception
     * @return array
     */
    public function createConfigByTable(Zend_Db_Adapter_Abstract $dbAdapter , $tableName , $adapterPrefix = false)
    {
        $config = array();
        
        if($adapterPrefix && strpos($tableName, $adapterPrefix) === 0)
        {
            $config['table'] = substr($tableName, strlen($adapterPrefix));
            $config['use_db_prefix'] = true;
        }
        else
        {
            $config['table'] = $tableName;
            $config['use_db_prefix'] = false;
        }
        
        $config['readonly'] = false;
        $config['system'] = false;
        $config['locked'] = false;
        $config['disable_keys'] = false;
        $config['rev_control'] = false;
        $config['save_history'] = true;
      
        $primary = $this->findPrimaryKey($dbAdapter, $tableName);
        if(!$primary)
            return false;
                
        $config['primary_key'] = $primary['COLUMN_NAME'];
        $config['link_title'] = $primary['COLUMN_NAME'];

        $desc = $dbAdapter->describeTable($tableName);
        $engine = $dbAdapter->fetchRow('SHOW TABLE STATUS WHERE `Name` = "' . $tableName . '"');
        $indexes = $dbAdapter->fetchAll('SHOW INDEX FROM `' . $tableName . '`');
        
        $index = array();
        $indexGroups = array();
        foreach($indexes as $k => $v)
        {
            if(strtolower($v['Column_name']) == $config['primary_key'])
                continue;
            
            $flag = false;
            if(!empty($index))
                foreach($index as $key => &$val)
                {
                    if($key == $v['Key_name'])
                    {
                        $val['columns'][] = $v['Column_name'];
                        $flag = true;
                        
                        if(!$v['Non_unique'])
                            $indexGroups[$v['Column_name']][] = $v['Key_name'];
                        
                        break;
                    }
                }
            
            unset($val);
            if($flag)
                continue;
            
            if($v['Index_type'] == 'FULLTEXT')
                $index[$v['Key_name']]['fulltext'] = true;
            else
                $index[$v['Key_name']]['fulltext'] = false;
            
            /**
    		 * Non_unique 
			 * 0 if the index cannot contain duplicates, 1 if it can.
    		 */
            if($v['Non_unique'])
            {
                $index[$v['Key_name']]['unique'] = false;
            }
            else
            {
                $index[$v['Key_name']]['unique'] = true;
                $indexGroups[$v['Column_name']][] = $v['Key_name'];
            }
            
            $index[$v['Key_name']]['columns'] = array( $v['Column_name']);
        }
        
        $fields = array();
        $objectFields = array();
        foreach($desc as $k => $v)
        {
            if(strtolower($v['COLUMN_NAME']) == $config['primary_key'])
                continue;

            $objectFields[$v['COLUMN_NAME']] = array(
                'title' => $v['COLUMN_NAME'] , 
                'db_type' => strtolower($v['DATA_TYPE'])
            );
            
            $fieldLink = & $objectFields[$v['COLUMN_NAME']];
            
            if(!empty($v['LENGTH']))
                $fieldLink['db_len'] = $v['LENGTH'];
            
            if($v['DEFAULT'] !== null)
                $fieldLink['db_default'] = $v['DEFAULT'];
            
            if($v['NULLABLE'])
            {
               $fieldLink['db_isNull'] = true;
               $fieldLink['required'] = false;
            }
            else
            {
               $fieldLink['db_isNull'] = false;
               $fieldLink['required'] = true;
            }
            
            if($v['UNSIGNED'])
               $fieldLink['db_unsigned'] = true;
            
            if(!empty($v['SCALE']))
               $fieldLink['db_scale'] = $v['SCALE'];
            
            if(!empty($v['PRECISION']))
               $fieldLink['db_precision'] = $v['PRECISION'];
            
            if(array_key_exists((string) $v['COLUMN_NAME'] , $indexGroups))
               $fieldLink['unique'] = $indexGroups[$v['COLUMN_NAME']];
            
            if($v['IDENTITY'])
               $fieldLink['auto_increment'] = true;
            
            unset($fieldLink);
        }
        
        $config['engine'] = $engine['Engine'];
        $config['fields'] = $objectFields;
 
        if(!empty($index))
            $config['indexes'] = $index;
        
        return $config;
    }
}
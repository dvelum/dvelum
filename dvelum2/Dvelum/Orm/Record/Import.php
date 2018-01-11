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

namespace Dvelum\Orm\Record;
use Dvelum\Db;
/**
 * Import component, experimental class
 * @package ORM
 * @subpackage Object
 * @license General Public License version 3
 * @example
 */
class Import
{
    protected $_errors = array();
    
    public function getErrors()
    {
        return $this->_errors;
    }
    
    /**
     * Find PRIMARY KEY
     * @param Db\Adapter $db
     * @param string $table
     * @return array | boolean (false)
     */
    public function findPrimaryKey(Db\Adapter $db , string $table)
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
     * @param \Db_Adapter $db
     * @param string $table
     * @return boolean
     */
    public function isValidPrimaryKey(Db\Adapter $db , string $table)
    {     
        $primary = $this->findPrimaryKey($db, $table);

        if(!$primary){
            $this->_errors[] = 'No primary key';
            return false;
        } 
               
        $dataType = strtolower($primary['DATA_TYPE']);
        
        if(!in_array($dataType , Builder::$numTypes , true)){
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
     * @param \Db_Adapter $dbAdapter
     * @param string $tableName
     * @param mixed $adapterPrefix, optional default - false
     * @throws Exception
     * @return array
     */
    public function createConfigByTable(Db\Adapter $dbAdapter , string $tableName , $adapterPrefix = false)
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
<?php
use Dvelum\Orm;
use Dvelum\Orm\Record\Builder;
use Dvelum\Db\Adapter;

class Backend_Designer_Import
{
    /**
     *
     * @param string $objectName            
     * @param array $fields - fields to check
     * @return array|bool on error
     */
    static public function checkImportORMFields($objectName , array $fields)
    {
        if(!$objectName || empty($fields))
            return false;
        
        $data = array();
        $config = Orm\Record::factory($objectName)->getConfig();
        foreach($fields as $field)
        {
            if(!$config->fieldExists($field)){
                continue;
            }
            
            $fieldConfig = $config->getFieldConfig($field);
            
            $o = Ext_Factory::object('Data_Field',array(
               'name'=>$field,
               'type' => self::convetDBFieldTypeToJS($fieldConfig['db_type'])
            ));
                      
            switch($fieldConfig['db_type'])
            {
                case 'date': $o->dateFormat = 'Y-m-d';
                    break;
                case 'datetime': $o->dateFormat = 'Y-m-d H:i:s';
                    break;
                case 'time': $o->dateFormat = 'H:i:s';    
                    break;
                          
            }
                                  
            $data[] = $o;
        }
        return $data;
    }

    /**
     *
     * @param Adapter $db
     * @param array $fields - fields to check
     * @param string $table            
     * @return array|bool on error
     */
    static public function checkImportDBFields(Adapter $db , array $fields , $table)
    {
        if(empty($fields) || ! strlen($table))
            return false;

        $meta = $db->getMeta();
        $data = [];

        $desc = $meta->getColumns($table);

        $dbFields = array_keys($desc);
        
        foreach($fields as $field)
        {         
            if(!in_array($field , $dbFields , true))
                return false;
            
            $o = Ext_Factory::object('Data_Field',array(
                    'name' => $field,
                    'type' => self::convetDBFieldTypeToJS($desc[$field]->getDataType())
            ));
            
            switch($desc[$field]->getDataType())
            {
            	case 'date': $o->dateFormat = 'Y-m-d';
            	    break;
            	case 'datetime': $o->dateFormat = 'Y-m-d H:i:s';
            	    break;  
            	case 'time' : $o->dateFormat = 'H:i:s';          
            }            
            $data[] = $o;
        }
        return $data;
    }

    /**
     *
     * @param string $type            
     * @return string
     */
    static public function convetDBFieldTypeToJS($type)
    {
        switch($type){
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
            case 'bit':
                $type = 'integer';
                break;
            
            case 'boolean':
                $type = 'boolean';
                break;
            
            case 'real':
            case 'float':
            case 'double':
            case 'decimal':
                $type = 'float';
                break;
            
            case 'date':
            case 'timestamp':
            case 'datetime':
            case 'time':
                $type = 'date';
                break;
            
            default:
                $type = 'string';
                break;
        }
        return $type;
    }


    /**
     * Convert db column info (from Zend_Db describe table) into ExtJs Field
     * 
     * @param array $info            
     * @return Ext_Object or false
     */
    static public function convertDbFieldToExtField($info)
    {
        $type = strtolower($info['data_type']);
        $newField = false;
        
        /*
         * Boolean
         */
        if($type === 'boolean')
        {
            $newField = Ext_Factory::object('Form_Field_Checkbox');
        }
        /*
         * Integer
         */
        elseif(in_array($type , Builder::$intTypes , true) || $type === 'timestamp')
        {
            $newField = Ext_Factory::object('Form_Field_Number');
            $newField->allowDecimals = false;
        }
        /*
         * Float
         */
        elseif(in_array($type , Builder::$floatTypes , true))
        {
            $newField = Ext_Factory::object('Form_Field_Number');
            $newField->allowDecimals = true;
            $newField->decimalSeparator = ',';
            $newField->decimalPrecision = 2;
        }
        /*
         * String
         */
        elseif(in_array($type , Builder::$charTypes , true))
        {
            $newField = Ext_Factory::object('Form_Field_Text');
        }
        /*
         * Text
         */
        elseif(in_array($type , Builder::$textTypes , true))
        {
            $newField = Ext_Factory::object('Form_Field_Textarea');
        }
        /*
         * Date time
         */
        elseif(in_array($type , Builder::$dateTypes , true))
        {
            switch($type){
                case 'date':
                    $newField = Ext_Factory::object('Form_Field_Date');
                    $newField->format = 'Y-m-d';
                    $newField->submitFormat = 'Y-m-d';
                    $newField->altFormats = 'Y-m-d';
                    break;
                case 'datetime':
                case 'timestamp':
                    $newField = Ext_Factory::object('Form_Field_Date');
                    $newField->format = 'Y-m-d H:i:s';
                    $newField->submitFormat = 'Y-m-d H:i:s';
                    $newField->altFormats = 'Y-m-d H:i:s';
                    break;
                case 'time':                    
                    $newField = Ext_Factory::object('Form_Field_Time');
                    $newField->format = 'H:i:s';
                    $newField->submitFormat = 'H:i:s';
                    $newField->altFormats = 'H:i:s';
                    break;
            }
        }
        /*
         * Undefined type
         */
        else
        {
            $newField = Ext_Factory::object('Form_Field_Text');
        }
        
        if($newField)
            $newField->fieldLabel = $info['name'];
        
        return $newField;
    }

    /**
     * Convert orm field into ext object
     * 
     * @param string $name            
     * @param array $fieldConfig
     *            - field info from Db_Object_Config
     * @return Ext_Object or false
     */
    static public function convertOrmFieldToExtField($name , $fieldConfig , $controllerUrl = '')
    {
        $type = $fieldConfig['db_type'];
        $newField = false;
        
        /*
         * Adapter
         */
        if(isset($fieldConfig['type']) && $fieldConfig['type'] === 'link')
        {
            if($fieldConfig['link_config']['link_type'] == 'dictionary'){
                $newField = Ext_Factory::object('Component_Field_System_Dictionary');
                if($fieldConfig['required']){
                  $newField->forceSelection = true;
                }else{
                  $newField->forceSelection = false;
                }
                $newField->dictionary = $fieldConfig['link_config']['object'];
            }elseif($fieldConfig['link_config']['link_type'] == Orm\Record\Config::LINK_OBJECT && $fieldConfig['link_config']['object'] == 'medialib'){
                $newField = Ext_Factory::object('Ext_Component_Field_System_Medialibitem');
            }elseif($fieldConfig['link_config']['link_type'] == Orm\Record\Config::LINK_OBJECT){
                $newField = Ext_Factory::object('Ext_Component_Field_System_Objectlink');
                $newField->objectName = $fieldConfig['link_config']['object'];
            }elseif($fieldConfig['link_config']['link_type'] == Orm\Record\Config::LINK_OBJECT_LIST){
                $newField = Ext_Factory::object('Ext_Component_Field_System_Objectslist');
                $newField->objectName = $fieldConfig['link_config']['object'];
            }else{
                $newField = Ext_Factory::object('Form_Field_Text');
            }
        }
        /*
         * Boolean
         */
        elseif($type === 'boolean')
        {
            $newField = Ext_Factory::object('Form_Field_Checkbox');
            $newField->inputValue = 1;
            $newField->uncheckedValue = 0;
        }
        /*
         * Integer
         */
        elseif(in_array($type , Builder::$intTypes , true))
        {
            $newField = Ext_Factory::object('Form_Field_Number');
            $newField->allowDecimals = false;
        }
        /*
         * Float
         */
        elseif(in_array($type , Builder::$floatTypes , true))
        {
            $newField = Ext_Factory::object('Form_Field_Number');
            $newField->allowDecimals = true;
            $newField->decimalSeparator = ',';
            
            if(isset($fieldConfig['db_precision']))
              $newField->decimalPrecision = $fieldConfig['db_precision'];
            else
              $newField->decimalPrecision = 2;
        }
        /*
         * String
         */
        elseif(in_array($type , Builder::$charTypes , true))
        {
            $newField = Ext_Factory::object('Form_Field_Text');
        }
        /*
         * Text
         */
        elseif(in_array($type , Builder::$textTypes , true))
        {
            if(isset($fieldConfig['allow_html']) && $fieldConfig['allow_html']){
                $newField = Ext_Factory::object('Component_Field_System_Medialibhtml');
                $newField->editorName = $name;
                $newField->title = $fieldConfig['title'];
                $newField->frame = false;
            }else{
                $newField = Ext_Factory::object('Form_Field_Textarea');
            }
        }
        /*
         * Date time
         */
        elseif(in_array($type , Builder::$dateTypes , true))
        {
            switch($type){
                case 'date':
                    $newField = Ext_Factory::object('Form_Field_Date');
                    $newField->format = 'Y-m-d';
                    $newField->submitFormat = 'Y-m-d';
                    $newField->altFormats = 'Y-m-d';
                    break;
                case 'datetime':
                case 'timestamp':
                    $newField = Ext_Factory::object('Form_Field_Date');
                    $newField->format = 'Y-m-d H:i:s';
                    $newField->submitFormat = 'Y-m-d H:i:s';
                    $newField->altFormats = 'Y-m-d H:i:s';
                    break;
                case 'time':
                    $newField = Ext_Factory::object('Form_Field_Time');
                    $newField->format = 'H:i:s';
                    $newField->submitFormat = 'H:i:s';
                    $newField->altFormats = 'H:i:s';
                    break;
            }
        }
        /*
         * Undefined type
         */
        else
        {
            $newField = Ext_Factory::object('Form_Field_Text');
        }
        
        $newFieldConfig = $newField->getConfig();
        
        if($newFieldConfig->isValidProperty('name'))
           $newField->name = $name;
              
        if(isset($fieldConfig['db_default']) && $fieldConfig['db_default']!==false && $newFieldConfig->isValidProperty('value'))
          $newField->value  = $fieldConfig['db_default'];
         
        if(in_array($type, Builder::$numTypes , true) && isset($fieldConfig['db_unsigned']) && $fieldConfig['db_unsigned'] && $newFieldConfig->isValidProperty('minValue'))
          $newField->minValue = 0;
                           
        if($newField->getClass() != 'Component_Field_System_Medialibhtml' && $newFieldConfig->isValidProperty('fieldLabel'))
          $newField->fieldLabel = $fieldConfig['title'];
        
        if($newField->getClass() === 'Component_Field_System_Objectslist')
          $newField->title = $fieldConfig['title'];
        
        if(isset($fieldConfig['required']) && $fieldConfig['required'] && $newFieldConfig->isValidProperty('allowBlank'))
          $newField->allowBlank = false;
        
        return $newField;
    }
}
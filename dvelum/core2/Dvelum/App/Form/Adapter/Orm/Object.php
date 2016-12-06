<?php

namespace Dvelum\App\Form\Adapter\Orm;

use Dvelum\App\Form;
use Dvelum\Orm;

class Object extends Form\Adapter
{
    protected $object;

    public function validateRequest(): bool
    {
        if(empty($this->config->get('orm_object'))){
            throw new \Exception(get_called_class().': orm_object is not set');
        }

        $this->object = null;

        $id = $this->request->post(
            $this->config->get('idField'),
            $this->config->get('idFieldType'),
            $this->config->get('idFieldDefault')
        );

        try{
            $obj = Orm\Object::factory($this->config->get('orm_object'), $id);
        }catch(\Exception $e){
             $this->errors[] = new Form\Error($this->lang->get('CANT_EXEC'), null, 'init_object');
             return false;
        }

        $acl = $obj->getAcl();

        if($acl && !$acl->canEdit($obj)){
            $this->errors[] = new Form\Error($this->lang->get('CANT_MODIFY'), null, 'acl_cant_edit');
            return false;
        }


        $posted = $this->request->postArray();

        $fields = $obj->getFields();
        $objectConfig = $obj->getConfig();
        $systemFields = $objectConfig->getSystemFieldsConfig();

        foreach($fields as $name)
        {
            /*
             * skip primary field
             */
            if($name == $this->config->get('idField'))
                continue;


            $field = $objectConfig->getField($name);

            if($field->isRequired() &&  !isset($systemFields[$name]) &&  (!isset($posted[$name]) || !strlen($posted[$name])))
            {
                $this->errors[] = new Form\Error($this->lang->get('CANT_BE_EMPTY'), $name);
                continue;
            }

            if($field->isBoolean() && !isset($posted[$name]))
                $posted[$name] = false;

            if(($field->isNull() || $field->isDateField()) && isset($posted[$name]) && empty($posted[$name]))
                $posted[$name] = null;


            if(!array_key_exists($name , $posted))
                continue;

            if(!$id && ( (is_string($posted[$name]) && !strlen((string)$posted[$name])) || (is_array($posted[$name]) && empty($posted[$name])) ) && $field->hasDefault())
                continue;

            try{
                $obj->set($name , $posted[$name]);
            }catch(\Exception $e){
                $this->errors[] = new Form\Error($this->lang->get('INVALID_VALUE'), $name);
            }
        }

        if(!empty($this->errors)){
            return false;
        }

        if($this->config->get('validateUnique'))
        {
            $errorList = $obj->validateUniqueValues();
            if(!empty($errorList)){
                foreach ($errorList as $field){
                    $this->errors[] = new Form\Error($this->lang->get('SB_UNIQUE'), $field);
                }
                return false;
            }
        }

        if($id){
            $obj->setId($id);
        }

        $this->object = $obj;
        return true;
    }

    /**
     * @return Orm\Object
     */
    public function getData() : Orm\Object
    {
        return $this->object;
    }
}
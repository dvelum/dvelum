<?php

use Dvelum\Orm;

class Sysdocs_Historyid
{
    public function getHid(Orm\Object $object)
    {
        $name = strtolower($object->getName());
        switch ($object->getName())
        {
            case 'sysdocs_class':
                return $this->getClassHid($object->get('fileHid') , $object->get('name'));
                break;
            case 'sysdocs_class_method':
                return $this->getMethodHid($object->get('classHid') , $object->get('name'));
                break;
            case 'sysdocs_class_method_param':
                return $this->getParamHid($object->get('methodHid') , $object->get('name'));
                break;
            case 'sysdocs_class_property':
                return $this->getPropertysHid($object->get('classHid') , $object->get('name'));
                break;
            case 'sysdocs_file':
                return $this->getFileHid($object->get('path') , $object->get('name'));
                break;

            default: throw new Exception('Undefined HID generator for '.$name);
        }
    }

    public function getClassHid($fileHid , $className)
    {
        return md5($fileHid. $className);
    }
    public function getMethodHid($classHid , $methodName)
    {
        return md5($classHid . $methodName);
    }
    public function getParamHid($methodHid , $paramName)
    {
        return md5($methodHid . $paramName);
    }
    public function getPropertysHid($classHid , $propertyName)
    {
        return md5($classHid . $propertyName);
    }
    public function getFileHid($path , $fileName)
    {
        return md5($path.$fileName);
    }
}
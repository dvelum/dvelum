<?php

class Ext_Component_JSObject extends Ext_Object
{
    protected $data;

    public function addItemProperty($name, $type, $value)
    {
       $this->data[$name] = ['name'=>$name, 'type'=>$type,'value'=>$value];
    }

    public function getItemProperty($name)
    {
        $this->data[$name];
    }

    public function isValidItemProperty($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Get JS code for components description (Objects used as Classes)
     * @param string | boolean $namespace
     * @return string
     */
    public function getDefineJs($namespace = false)
    {
        if($namespace)
            $name = $namespace.'.'.$this->getName();
        else
            $name = $this->getName();

        $name.' = '.$this->__toString();
    }

    public function __toString()
    {
        $data = $this->data;
        if(!empty($this->data)){
            foreach ($this->data as $k=>&$v){
                switch ($v['type']){
                    case 'Number':
                        $v = $k.':'.floatval($v);
                        break;
                    case 'String':
                        $v = $k.':"'.urlencode(strval($v)).'"';
                        break;
                    case 'Object':
                        $v = $k.':'.strval($v);
                        break;
                    case 'Boolean':
                        if(boolval($v)){
                            $v = $k.':true';
                        }else{
                            $v = $k.':false';
                        }
                        break;
                    default:
                        $v = $k.':null';
                }
            }unset($v);
        }else{
            return '{}';
        }

        return '{'.implode(':', array_values($data)).'}';
    }
}
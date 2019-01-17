<?php

class Ext_Component_JSObject extends Ext_Object
{
    protected $data;

    public function addItemProperty($name, $type, $value)
    {
       $this->data[$name] = ['key'=>$name, 'type'=>$type,'value'=>$value];
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

        return "\n".$name.' = '.$this->__toString().';';
    }

    public function __toString()
    {
        $data = $this->_config->data;
        if(!empty($data))
        {
            $data = json_decode($data, true);
            $result = [];
            foreach ($data as $v)
            {
                switch ($v['type'])
                {
                    case 'Number':
                        $result[] = $v['key'].':'.floatval($v['value']);
                        break;
                    case 'String':
                        $result[] = $v['key'].':"'.addcslashes(strval($v['value']),'"').'"';
                        break;
                    case 'Object':
                        $result[] = $v['key'].':'.strval($v['value']);
                        break;
                    case 'Boolean':
                        if(boolval($v['value'])){
                            $result[] = $v['key'].':true';
                        }else{
                            $result[] = $v['key'].':false';
                        }
                        break;
                   // default:
                   //     $k.':null';
                }
            }unset($v);
            $data = $result;
        }else{
            return '{}';
        }

        return "{\n\t".implode(",\n\t", $data)."\n}";
    }
}
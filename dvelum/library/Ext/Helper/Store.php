<?php
class Ext_Helper_Store implements Ext_Exportable
{
    const TYPE_STORE = 'store';
    const TYPE_INSTANCE = 'instance';
    const TYPE_JSCODE = 'jscall';

    protected $type = 'store';
    protected $value = '';

    /**
     * (non-PHPdoc)
     * @see Ext_Object::getClass()
     */
    public function getClass()
    {
        return get_called_class();
    }

    /**
     * Set renderer type
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Set renderer value
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get renderer type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get renderer value
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get accepted types
     * @return array
     */
    public function getTypes()
    {
        return [
            self::TYPE_STORE,
            self::TYPE_INSTANCE,
            self::TYPE_JSCODE,
        ];
    }

    public function getState(){
        return [
            'type' => $this->type,
            'value' => $this->value
        ];
    }

    public function setState(array $state){
        if(!empty($state['type']))
            $this->type = $state['type'];
        if(!empty($state['value']))
            $this->value = $state['value'];
    }

    public function __toString()
    {
        $string = '';
        switch($this->type){

            case self::TYPE_JSCODE:
            case self::TYPE_STORE:
                $string = $this->value;
                break;
            case self::TYPE_INSTANCE:
                $string = Designer_Project_Code::$NEW_INSTANCE_TOKEN . $this->value;
                break;

        }
        return $string;
    }
}
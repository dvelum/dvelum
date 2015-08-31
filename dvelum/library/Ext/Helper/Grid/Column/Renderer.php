<?php
class Ext_Helper_Grid_Column_Renderer
{
    const TYPE_JSCALL = 'jscall';
    const TYPE_JSCODE = 'jscode';
    const TYPE_ADAPTER = 'adapter';
    const TYPE_DICTIONARY = 'dictionary';

    protected $type = 'adapter';
    protected $value = '';

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

    public function __toString()
    {
        $string = '';

        switch($this->type){
            case self::TYPE_ADAPTER:
                if(class_exists($this->value)){
                    $o = new $this->value();
                    $string = $o->__toString();
                }
                break;
            case self::TYPE_JSCALL:
                $string = $this->value;
                break;
            case self::TYPE_JSCODE:
                $string='function(value, metaData, record, rowIndex, colIndex, store, view){
                ' . $this->value .'
                }';
                break;
            case self::TYPE_DICTIONARY:
                $dictionaryRenderer = new Ext_Component_Dictionary_Renderer($this->value);
                $string = $dictionaryRenderer->__toString();
                break;
        }
        return $string;
    }

    public function getTypes()
    {
        return [
            self::TYPE_ADAPTER,
            self::TYPE_JSCALL,
            self::TYPE_JSCODE,
            self::TYPE_DICTIONARY
        ];
    }
}
<?php

class Designer_Project_Methods_Item
{
    protected $_name;
    protected $_code = '';
    protected $_description = '';
    protected $_params = array();

    /**
     * Constructor
     * @param string $name
     * @param array $params , optional array(array('type'=>'' , name=>''))
     */
    public function __construct($name, $params = false)
    {
        $this->setName($name);
        if (is_array($params) && !empty($params))
            $this->addParams($params);
    }

    /**
     * Set method name
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Get method Name
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get method params
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Remove params
     */
    public function removeParams()
    {
        $this->_params = array();
    }

    /**
     * Set method params
     * @param array $params array(array('type'=>'' , name=>''))
     */
    public function setParams(array $params)
    {
        $this->removeParams();
        $this->addParams($params);
    }

    /**
     * Add parametr
     * @param string $name
     * @param string $type , optional
     */
    public function addParam($name, $type = '')
    {
        $this->_params[] = array('name' => $name, 'type' => $type);
    }

    /**
     * Add method parametrs
     * @param array $data array(array('type'=>'' , name=>''))
     */
    public function addParams(array $data)
    {
        if (empty($data))
            return;

        foreach ($data as $v) {
            $this->addParam($v['name'], $v['type']);
        }
    }

    /**
     * Remove method parametr
     * @param integer $index
     */
    public function removeParam($index)
    {
        if (empty($this->_params))
            return;

        $new = array();
        foreach ($this->_params as $paramIndex => $data) {
            if ($paramIndex == $index)
                continue;
            $new[] = $data;
        }
        $this->_params = $new;
    }

    /**
     * Set method code
     * @param string $code
     */
    public function setCode($code)
    {
        $this->_code = $code;
    }

    /**
     * Get method code
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Get params list as string for description
     * @return string
     */
    public function getParamsAsDescription()
    {
        if (empty($this->_params))
            return '';

        $params = array();

        if (!empty($this->_params))
            foreach ($this->_params as $item)
                $params[] = $item['type'] . ' ' . $item['name'];

        return implode(' , ', $params);
    }

    /**
     * Set method description
     * @param string $text
     */
    public function setDescription($text)
    {
        $this->_description = $text;
    }

    /**
     * Get Method description
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Get method JsDoc string
     */
    public function getJsDoc()
    {
        $description = "";
        $descLines = explode("\n", $this->_description);

        if (!empty($descLines))
            $description = "* " . implode("\n * ", $descLines);

        $s = "/**\n " . $description . "\n *\n";

        if (!empty($this->_params))
            foreach ($this->_params as $param)
                $s .= " * @param " . $param['type'] . " " . $param['name'] . "\n";

        $s .= " */";
        return $s;
    }

    /**
     * Conver method data into array
     * @return array
     */
    public function toArray()
    {
        return array(
            'name' => $this->_name,
            'description' => $this->_description,
            'code' => $this->_code,
            'params' => $this->_params,
            'paramsLine' => $this->getParamsAsDescription(),
            'jsdoc' => $this->getJsDoc()
        );
    }

    /**
     * Get params line (for js code)
     * @return string
     */
    public function getParamsLine()
    {
        if (empty($this->_params))
            return '';

        $params = array();

        if (!empty($this->_params))
            foreach ($this->_params as $item)
                $params[] = $item['name'];

        return implode(' , ', $params);
    }
}
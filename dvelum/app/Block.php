<?php
/**
 * Base class for page blocks
 * @author Kirill A Egorov 2011 DVelum project
 */
abstract class Block
{
    /**
     * Block config
     * @var array
     */
    protected $_config;
    /**
     * Block template. The path is relative to theme location
     * @var string
     */
    protected $_template = 'block.php';
    protected $_params = array();

    /**
     * Allow cache for block content (frontend hard cache)
     * @var boolean
     */
    const cacheable = false;
    /**
     * Block render result depends on the page on which it is located
     * @var boolean
     */
    const dependsOnPage = false;

    /**
     * Block constructor
     * @param array $config - block config
     */
    public function __construct(array $config)
    {
        $this->_config = $config;

        if(!isset($config['params']) || !strlen($config['params']))
            return;

        $parts = explode(',' , $config['params']);

        if(!empty($parts))
        {
            foreach($parts as $item)
            {
                $config = explode('=' , str_replace(' ' , '' , $item));
                if(is_array($config) && count($config) == 2)
                    $this->_params[$config[0]] = $config[1];
            }
        }

    }

    /**
     * Render block content
     * @return string
     */
    abstract public function render();

    /**
     * String representation for object
     * Returns rendered html
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
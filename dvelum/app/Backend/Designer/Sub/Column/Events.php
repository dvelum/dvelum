<?php
abstract class Backend_Designer_Sub_Column_Events extends Backend_Designer_Sub
{
    /**
     * @var Designer_Project
     */
    protected $_project;
    /**
     * @var Ext_Grid
     */
    protected $_object;
    /**
     * @var Ext_Grid_Column_Action
     */
    protected $_column;

    public function __construct()
    {
        parent::__construct();
        $this->_checkLoaded();
        $this->_checkObject();
        $this->_checkColumn();
    }

    protected function _checkObject()
    {
        $name = Request::post('object', 'string', '');
        $project = $this->_getProject();
        if(!strlen($name) || !$project->objectExists($name) || $project->getObject($name)->getClass()!=='Grid')
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $this->_project = $project;
        $this->_object = $project->getObject($name);
    }

    protected function _checkColumn()
    {
        $object = $this->_object;
        $column = Request::post('columnId','string',false);

        if($column === false || $object->getClass()!=='Grid' || !$object->columnExists($column))
            Response::jsonError('Cant find column');

        $columnObject = $object->getColumn($column);

        $this->_column = $columnObject;
    }

    protected function _convertParams($config)
    {
        if(empty($config))
            return '';

        $paramsArray = [];

        foreach ($config as $pName=>$pType)
            $paramsArray[] = '<span style="color:green;">' . $pType . '</span> ' . $pName;

        return implode(' , ', $paramsArray);

    }

    protected function _getEvent()
    {
        $event = Request::post('event', 'string', false);
        if(!strlen($event) || $event === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        return $event;
    }
}
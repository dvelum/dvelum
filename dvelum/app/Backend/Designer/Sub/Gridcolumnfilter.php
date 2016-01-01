<?php
class Backend_Designer_Sub_Gridcolumnfilter extends Backend_Designer_Sub
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
        $column = Request::post('column','string',false);

        if($column === false || $object->getClass()!=='Grid' || !$object->columnExists($column))
            Response::jsonError('Cant find column');

        $columnObject = $object->getColumn($column);

        $this->_column = $columnObject;
    }

    /**
     * Get column filter type
     */
    public function gettypeAction()
    {
        $this->_checkColumn();

        $filter = $this->_column->filter;
        $type = '';

        if(!empty($filter) && $filter instanceof Ext_Grid_Filter){
            $type = $filter->getType();
        }
        Response::jsonSuccess(['type'=>$type]);
    }

    /**
     * Set column filter type
     */
    public function settypeAction()
    {
        $this->_checkColumn();
        $type = Request::post('type','string', false);

        if(empty($type)){
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }

        $filter = $this->_column->filter;

        if(empty($filter)){
            $filter = Ext_Factory::object('Grid_Filter_'.ucfirst($type));
            $filter->setName($this->_column->getName().'_filter');
        }else{
            if($type !== $filter->getType()){
                $f = Ext_Factory::object('Grid_Filter_'.ucfirst($type) , $filter->getConfig()->__toArray(true));
                $filter = $f;
            }
        }

        if($filter->getType() == 'date' && empty($filter->fields)){
            $filter->fields  = '{lt: {text: appLang.FILTER_BEFORE_TEXT}, gt: {text: appLang.FILTER_AFTER_TEXT}, eq: {text: appLang.FILTER_ON_TEXT}}';
        }

        $this->_column->filter = $filter;
        $this->_storeProject();
        Response::jsonSuccess();
    }

    /**
     * Get filter properties
     */
    public function listAction()
    {
        $this->_checkColumn();
        $filter = $this->_column->filter;
        $data = [];
        if(!empty($filter) && $filter instanceof Ext_Grid_Filter){
            $data = $filter->getConfig()->__toArray();
        }
        unset($data['type']);
        Response::jsonSuccess($data);
    }

    /**
     * Set filter property
     */
    public function setpropertyAction()
    {
        $this->_checkColumn();
        $property = Request::post('name', 'string', false);
        $value = Request::post('value', 'raw', false);

        $filter = $this->_column->filter;

        if(empty($filter) ||  !$filter instanceof Ext_Grid_Filter){
            Response::jsonError('undefined filter');
        }

        if(!$filter->isValidProperty($property))
            Response::jsonError('undefined property '.$property);

        $filter->$property = $value;
        $this->_storeProject();
        Response::jsonSuccess();
    }

    /**
     * Remove column filter
     */
    public function removeFilterAction()
    {
        $this->_checkColumn();
        $this->_column->filter  = null;
        $this->_storeProject();
        Response::jsonSuccess();
    }
}
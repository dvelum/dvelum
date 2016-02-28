<?php
/**
 * Should be called from backend controller
 */
class Backend_Orm_Dataview_Editor extends Backend_Controller_Crud
{
    public function __construct()
    {
        parent::__construct();
        $this->_user = User::getInstance();
    }

    public function getModule()
    {
        $dataObject = Request::post('d_object', 'string', false);

        if(!$dataObject || !Db_Object_Config::configExists($dataObject))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        return ucfirst($dataObject);
    }

    public function getObjectName()
    {
        return $this->getModule();
    }


    public function indexAction(){}
    public function includeScripts(){}
    public function checkAuth(){}
    protected function _checkCanEdit()
    {
        return true;
    }
    protected function _checkCanDelete()
    {
        return true;
    }
}
<?php
class Backend_Extrenals_Controller extends Backend_Controller
{
    /**
     * @var Externals_Manager
     */
    protected $externalsManager;

    public function __construct()
    {
        parent::__construct();
        $this->externalsManager =  Externals_Manager::factory();
    }

    /**
     * Get list of available external modules
     */
    public function listAction()
    {
        $result = [];

        if($this->externalsManager->hasModules()){
            $result = $this->externalsManager->getModules();
        }

        foreach($result as $k=>&$v) {
            unset($v['autoloader']);
        }unset($v);

        Response::jsonSuccess($result);
    }

    /**
     * Reinstall external module
     */
    public function reinstallAction()
    {
        $id = Request::post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        if(!$this->externalsManager->install($id , true)) {
            $errors = $this->externalsManager->getErrors();
            Response::jsonError($this->_lang->get('CANT_EXEC').' '.implode(', ', $errors));
        }

        Response::jsonSuccess();
    }

    /**
     * Enable external module
     */
    public function enableAction()
    {
        $id = Request::post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        if(!$this->externalsManager->setEnabled($id , true)){
            $errors = $this->externalsManager->getErrors();
            Response::jsonError($this->_lang->get('CANT_EXEC').' '.implode(', ', $errors));
        }

        Response::jsonSuccess();
    }

    /**
     * Disable external module
     */
    public function disableAction()
    {
        $id = Request::post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        if(!$this->externalsManager->setEnabled($id , false)){
            $errors = $this->externalsManager->getErrors();
            Response::jsonError($this->_lang->get('CANT_EXEC').' '.implode(', ', $errors));
        }
        Response::jsonSuccess();
    }

    /**
     * Unistall external module
     */
    public function deleteAction()
    {
        $id = Request::post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->uninstall($id)){
            $errors = $this->externalsManager->getErrors();
            Response::jsonError($this->_lang->get('CANT_EXEC').' '.implode(', ', $errors));
        }
        Response::jsonSuccess();
    }
}
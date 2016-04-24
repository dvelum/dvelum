<?php
class Backend_Extrenals_Controller extends Backend_Controller
{
    /**
     * Get list of available external modules
     */
    public function listAction()
    {
        $externalsManager = Externals_Manager::factory();
        $result = [];

        if($externalsManager->hasModules()){
            $result = $externalsManager->getModules();
        }

        foreach($result as $k=>&$v) {
            unset($v['autoloader']);
        }unset($v);

        Response::jsonSuccess($result);
    }

    public function reinstallAction()
    {
        $id = Request::post('id', Filter::FILTER_STRING, false);
        $externalsManager = Externals_Manager::factory();

        if(!$externalsManager->moduleExists($id)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

    }
}
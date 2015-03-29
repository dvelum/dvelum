<?php
class Backend_Docs_Controller extends Backend_Controller implements Router_Interface
{
    /**
     * (non-PHPdoc)
     * @see Router::run()
     */
    public function route()
    {
        $controller = new Sysdocs_Controller($this->_configMain ,2 , false);
        $controller->setCanEdit(User::getInstance()->canEdit($this->_module));
        return $controller->run();
    }
    /**
     * (non-PHPdoc)
     * @see Backend_Controller::indexAction()
     */
    public function indexAction(){}
}
<?php
class Backend_Docs_Controller extends Backend_Controller implements Router_Interface
{
   /**
    * (non-PHPdoc)
    * @see Router::run()
    */
   public function run()
   {
     $controller = new Sysdocs_Controller($this->_configMain ,2 , false);
     return $controller->run();
   }
   /**
    * (non-PHPdoc)
    * @see Backend_Controller::indexAction()
    */
   public function indexAction(){}
}
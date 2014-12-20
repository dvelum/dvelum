<?php
class Frontend_Docs_Controller extends Frontend_Controller
{

  protected $docController;
  
  public function __construct()
  {
    $this->docsController = new Sysdocs_Controller($this->_configMain , 'content');
  }
    
  // Interface implementation
  public function indexAction()
  {
    $this->docsController->indexAction();
  }
  /**
   * (non-PHPdoc)
   * @see Sysdocs_Controller_Interface::apitreeAction()
   */
  public function apitreeAction()
  {
      $this->docsController->apitreeAction();
  }
  /**
   * non-PHPdoc)
   * @see Sysdocs_Controller_Interface::infoAction()
   */
  public function infoAction()
  {
      $this->docsController->infoAction();
  }
  /**
   * non-PHPdoc)
   * @see Sysdocs_Controller_Interface::configAction()
   */
  public function configAction()
  {
     $this->docsController->configAction();
  }
}
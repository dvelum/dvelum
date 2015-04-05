<?php
class Frontend_Docs_Controller extends Frontend_Controller implements Router_Interface
{
  protected $docsController;
  
  public function __construct()
  {
    $controller = new Sysdocs_Controller($this->_configMain ,1 , false);
    $controller->setCanEdit(User::getInstance()->canEdit(false));
    $this->docsController = $controller;
    parent::__construct();
  }
   /**
    * (non-PHPdoc)
    * @see Router::run()
    */
   public function route()
   {
     $request = Request::getInstance();

     if(!Request::isAjax()){
       $this->indexAction();
     }else{
       $this->docsController->run();
     }    
   }

  /**
   * (non-PHPdoc)
   * 
   * @see Backend_Controller::indexAction()
   */
  public function indexAction()
  {
    $template = new Template();
    $template->setData(array(
        'page' => $this->_page , 
        'resource' => $this->_resource
    ));
    
    $this->_page->page_title = 'DVelum Documentation';
    $this->_page->theme = 'docs';
    // $this->_page->text =
    // $template->render($this->_page->getTemplatePath('layout.php');
    $this->_resource->addJs('/js/application.js' , 1);
    $this->_resource->addInlineJs('
            Ext.ns("app");
            app.wwwRoot = "/";
            var canEdit = false; 
            app.delimiter = "' . $this->_configMain->get('urlDelimiter') . '";
            app.root = "' . $this->_configMain->get('wwwroot') . $this->_configMain->get('urlDelimiter') . 'docs' . $this->_configMain->get('urlDelimiter') . '";
     
         ');
    $this->docsController->run();
    $this->_router->showPage($this->_page , new Blockmanager());
  }
}
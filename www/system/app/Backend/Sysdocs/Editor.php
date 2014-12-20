<?php
class Backend_Sysdocs_Editor extends Sysdocs_Controller
{
  
  /**
   * Default action. Load UI
   */
  public function indexAction()
  {
      $this->includeScripts();
      Resource::getInstance()->addInlineJs('
           app.docLang = "'.$this->language.'";
           app.docVersion = "'.$this->version.'";
      ');
      $this->_runDesignerProject($this->configMain->get('configs').'layouts/system/sysdocs_localization.designer.dat', $this->container);
  }
}
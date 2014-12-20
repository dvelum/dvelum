<?php
class Backend_Index_Controller extends Backend_Controller
{  
    public function indexAction()
    {
    	if($this->_configMain['development'])
    		$this->_resource->addJs('/js/app/system/crud/devindex.js', 4);
    	else
    		$this->_resource->addJs('/js/app/system/crud/index.js', 4);
    	
    }

    public function devinfoAction()
    {	
    	$template = new Template();   
    	Response::put($template->render('./templates/system/default/devindex.php'));
    }
}
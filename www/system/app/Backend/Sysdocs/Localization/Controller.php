<?php
class Backend_Sysdocs_Localization_Controller extends Backend_Controller_Crud implements Router_Interface
{
  
  /**
   * (non-PHPdoc)
   * @see Router::run()
   */
  public function run()
  {
      $controller = new Backend_Sysdocs_Editor($this->_configMain ,2 , false);
      return $controller->run();
  }
  
    
// 	protected $_listFields = array("lang","field","object_id","vers","id","object_class");
//     protected $_canViewObjects = array("sysdocs_class","sysdocs_class_method","sysdocs_class_method_param","sysdocs_class_property");

//     public function __construct()
//     {
//     	parent::__construct();
//     	$this->fileModel = Model::factory('Sysdocs_File');
//     	$this->docConfig = Config::factory(Config::File_Array, $this->_configMain->get('configs').'sysdocs.php');
//     }
//     /**
//      * Get list of documentation versions
//      */
//     public function versionsAction()
//     {
//       $cfg = Config::factory(Config::File_Array, $this->_configMain->get('configs').'sysdocs.php');
//       $data = array();
//       $vList = $cfg->get('versions');

//       foreach ($vList as $k=>$v){
//       	$data[] = array('id'=>$v,'title'=>$k);
//       }
//       Response::jsonSuccess($data);
//     }

//     /**
//      * Get API tree.Panel data
//      */
//     public function apitreeAction()
//     {
//         $vers = $this->docConfig->get('default_version');
//         $vList = $this->docConfig->get('versions');
//         $this->versionIndex = $vList[$vers];
//     	Response::jsonArray($this->fileModel->getTreeList($this->versionIndex));
//     }
}
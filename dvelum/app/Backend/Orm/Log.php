<?php
class Backend_Orm_Log extends Backend_Controller
{
	public function getModule(){
		return 'Orm';
	}
	public function indexAction(){}
	
  /**
   * Get DB_Object_Builder log contents
   * for current development version
   */
  public function getlogAction()
  {
      $file = Request::post('file', Filter::FILTER_STRING, false);

      $logPath = $this->_configMain['orm_log_path'];
  
      //$fileName = $logPath . 'default_'.$version . '_build_log.sql';
      
      $fileName = $logPath . $file.'.sql';
      
      if(file_exists($fileName))
          $data = nl2br(file_get_contents($fileName));
      else
          $data = '';
      	
      Response::jsonSuccess($data);
  }
  
  public function getlogfilesAction()
  {
    //$version = $this->_configMain['development_version'];
    $logPath = $this->_configMain['orm_log_path'];  
    //$fileName = $logPath . 'default_'.$version . '_build_log.sql';   
    $files = File::scanFiles($logPath , array('.sql'), false);
    $data = array();
    foreach ($files as $file)
    {
      $file = basename($file , '.sql');
      $data[] = array('id'=>$file);
    }
    Response::jsonSuccess($data);
  }
}
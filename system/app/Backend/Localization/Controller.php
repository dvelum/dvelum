<?php
class Backend_Localization_Controller extends Backend_Controller_Crud
{
  /**
   * @var Backend_Localization_Manager
   */
  protected $_manager;

  public function __construct()
  {
    parent::__construct();
    $this->_manager = new Backend_Localization_Manager($this->_configMain);
  }

  /**
   * Get list of language disctionaries
   */
  public function langlistAction()
  {
    Response::jsonSuccess($this->_getLangs(false));
  }
  /**
   * Get list of  system locales
   */
  public function localeslistAction()
  {
    Response::jsonSuccess($this->_getLangs(true));
  }

  protected function _getLangs($onlyMain)
  {
    $langs = $this->_manager->getLangs($onlyMain);
    $result = array();

    foreach ($langs as $lang)
        $result[] = array('id'=>$lang);

    return $result;
  }

  /**
   * Get localization dictionary content
   */
  public function localisationAction()
  {
    $dictionary = Request::post('dictionary', Filter::FILTER_CLEANED_STR, false);

    if($dictionary === false)
      Response::jsonError($this->_lang->get('WRONG_REQUEST'));

    $data = $this->_manager->getLocalistaion($dictionary);

    Response::jsonSuccess($data);
  }

  /**
   * Rebuild localization index
   */
  public function rebuildindexAction()
  {
    try{
      $this->_manager->rebuildAllIndexes();
      Response::jsonSuccess();
    }catch (Exception $e){
      Response::jsonError($e->getMessage());
    }
  }

  public function addrecordAction()
  {
    $this->_checkCanEdit();
    $dictionary = Request::post('dictionary', Filter::FILTER_CLEANED_STR, false);
    $key = Request::post('key', Filter::FILTER_CLEANED_STR, false);
    $lang = Request::post('lang', Filter::FILTER_ARRAY, false);

    if($dictionary === false || empty($dictionary) ||  $key ===false || $lang===false){
      Response::jsonError($this->_lang->get('WRONG_REQUEST'));
    }

    try{
      $this->_manager->addRecord($dictionary , $key , $lang);
      $this->_router->runController('Backend_Compiler_Controller' , 'lang');
     // Response::jsonSuccess();
    }catch(Exception $e){
      Response::jsonError($e->getMessage());
    }
  }

  public function removerecordAction()
  {
    $this->_checkCanEdit();
    $dictionary = Request::post('dictionary', Filter::FILTER_CLEANED_STR, false);
    $id = Request::post('id', Filter::FILTER_CLEANED_STR, false);

    if($dictionary === false || empty($dictionary) || $id ===false){
        Response::jsonError($this->_lang->get('WRONG_REQUEST'));
    }
    try{
        $this->_manager->removeRecord($dictionary , $id);
        $this->_router->runController('Backend_Compiler_Controller' , 'lang');
       // Response::jsonSuccess();
    }catch(Exception $e){
        Response::jsonError($e->getMessage());
    }
  }

  public function updaterecordsAction()
  {
    $this->_checkCanDelete();
    $dictionary = Request::post('dictionary', Filter::FILTER_CLEANED_STR, false);
    $data = Request::post('data', Filter::FILTER_RAW, false);

    if($dictionary === false || empty($dictionary) ||  $data ===false){
        Response::jsonError($this->_lang->get('WRONG_REQUEST'));
    }

    $data = json_decode($data , true);

    try{
        $this->_manager->updateRecords($dictionary , $data);
        $this->_router->runController('Backend_Compiler_Controller' , 'lang');
        //Response::jsonSuccess();
    }catch(Exception $e){
        Response::jsonError($e->getMessage());
    }
  }
  /**
   * Create sub dictionary
   */
  public function createsubAction()
  {
  	 $name = Request::post('name', Filter::FILTER_ALPHANUM, false);

  	 if(empty($name))
  	 	Response::jsonError($this->_lang->get('INVALID_VALUE_FOR_FIELD').' '.$this->_lang->get('DICTIONARY_NAME'));

  	 if($this->_manager->dictionaryExists($name))
  	     Response::jsonError($this->_lang->get('DICTIONARY_EXISTS'));

  	 try{
  	     $this->_manager->createDictionary($name);
  	 } catch (Exception $e){
  	     Response::jsonError($e->getMessage());
  	 }

  	 Response::jsonSuccess();
  }
}
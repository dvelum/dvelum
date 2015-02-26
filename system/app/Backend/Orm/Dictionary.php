<?php
class Backend_Orm_Dictionary extends Backend_Controller
{
	public function getModule(){
		return 'Orm';
	}
	public function indexAction(){}
	
	/**
	 * Create new dictionary or rename existed
	 */
	public function updateAction()
    {
    	$this->_checkCanEdit();
    	$id = Request::post('id','string',false);
    	$name = strtolower(Request::post('name','string',false));
    	
    	$manager = new Dictionary_Manager();
    	if(!$name)
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    	
    	if(!$id){
    		if(!$manager->create($name)){
    			Response::jsonError($this->_lang->CANT_WRITE_FS .' '. $this->_lang->OR .' '. $this->_lang->DICTIONARY_EXISTS);
    		}
    	}else{
    		if(!$manager->rename($id, $name))
				Response::jsonError($this->_lang->CANT_WRITE_FS);
    	}
    		
    	Response::jsonSuccess();
    }
    /**
     * Remove dictionary
     */    
    public function removeAction()
    {
    	$manager = new Dictionary_Manager();
    	$this->_checkCanDelete();
    	$name = strtolower(Request::post('name','string',false));
    	if(empty($name))
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    		
		if(!$manager->remove($name))
			Response::jsonError($this->_lang->CANT_WRITE_FS);
    	else
    		Response::jsonSuccess();
    }
    /**
     * Get dictionary list
     */
    public function listAction()
    {
    	$manager = new Dictionary_Manager();
    	$data = array();
    	$list = $manager->getList();
    	
    	if(!empty($list))
    		foreach ($list as $v)
    			$data[] = array('id' => $v,'title' => $v);
    			
    	Response::jsonSuccess($data);
    }
    /**
     * Get dictionary records list
     */
    public function recordsAction()
    {
    	$name = strtolower(Request::post('dictionary','string',false));
    	if (empty($name))
    		Response::jsonError($this->_lang->WRONG_REQUEST);

    	$list = Dictionary::getInstance($name)->getData();
    	$data = array();
    	
    	if(!empty($list))
    		foreach ($list as $k=>$v)
    			$data[] = array('id' => $k,'key' => $k,'value' => $v);
    	Response::jsonSuccess($data);
    }
    /**
     * Update dictionary records
     */
    public function updaterecordsAction()
    {
    	$this->_checkCanEdit();
    	$dictionaryName = strtolower(Request::post('dictionary','string',false));
    	$data = Request::post('data','raw',false);
    	$data = json_decode($data, true);

    	if(empty($data) || !strlen($dictionaryName))
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    	
    	$dictionary = Dictionary::getInstance($dictionaryName);
    	foreach ($data as $v)
    	{
    		if($dictionary->isValidKey($v['key']) && $v['key'] != $v['id'])
    			Response::jsonError($this->_lang->WRONG_REQUEST);
    		
    		if(!empty($v['id']))
    			$dictionary->removeRecord($v['id']);
    		$dictionary->addRecord($v['key'], $v['value']);
    	}
    	if(!$dictionary->saveChanges())
    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    	Response::jsonSuccess();
    }
    /**
     * Remove dictionary record
     */
    public function removerecordsAction()
    {
    	$dictionaryName = strtolower(Request::post('dictionary','string',false));
    	$name = Request::post('name','string',false);
    	
    	if(!strlen($name) || !strlen($dictionaryName))
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    	
    	$dictionary = Dictionary::getInstance($dictionaryName);
    	$dictionary->removeRecord($name);
    	if(!$dictionary->saveChanges())
    		Response::jsonError($this->_lang->CANT_WRITE_FS);
    	Response::jsonSuccess();
    }
}
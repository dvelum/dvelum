<?php
class Backend_Apikeys_Controller extends Backend_Controller_Crud
{
	/**
	 * @var Model
	 */
	protected $_model;
	protected $_listFields = array('id','name','active');
	
	public function __construct()
	{
		parent::__construct();
		$this->_model = Model::factory($this->_objectName);
	}
    
    /**
     * (non-PHPdoc)
     * @see Backend_Controller_Crud::loaddataAction()
     */
    public function loaddataAction()
    {
    	$id = Request::post('id', 'int', false);
    	
    	if(!$id)
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    		
    	Response::jsonSuccess($this->_model->getItem($id, $this->_listFields));
    }
    
	
    /**
     * Checks the uniqueness of the name
     * @return void
     */
    public function checknameAction()
    {
    	$id = Request::post('id', 'int', 0);
    	$name = Request::post('name', 'str', false);
    	
    	if(!strlen($name))
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    	
    	if($this->_model->checkUnique($id, 'name', $name))
    		Response::jsonSuccess();
    	else
    		Response::jsonError($this->_lang->SB_UNIQUE);
    }
    
	/**
     * Checks the uniqueness of the name
     * @return void
     */
    public function checkhashAction()
    {
    	$id = Request::post('id', 'int', 0);
    	$hash = Request::post('hash', 'str', false);
    	
    	if(!strlen($hash))
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    	
    	if($this->_model->checkUnique($id, 'hash', Utils::hash($hash)))
    		Response::jsonSuccess();
    	else
    		Response::jsonError($this->_lang->SB_UNIQUE);
    }
    
    /**
     * Insert Object into DB
     * @param Db_Object $object
     * @return void
     */
    public function insertObject(Db_Object $object)
    { 
    	$object->set('hash', Utils::hash($object->get('hash')));
        if(!$recId = $object->save())
			Response::jsonError($this->_lang->CANT_CREATE);
                                   
		Response::jsonSuccess(array('id'=>$recId,));
    }
    
    /**
     * Update object
     * @param Db_Object $object
     * @return void
     */
    public function updateObject(Db_Object $object)
    {
    	$changeVal = Request::post('changeVal', 'bool', false);
    	
    	if($changeVal)
    		$object->set('hash', Utils::hash($object->get('hash')));
    		
        if(!$object->save())
           Response::jsonError($this->_lang->CANT_EXEC); 
             	  
        Response::jsonSuccess(array('id'=>$object->getId()));
    }
}
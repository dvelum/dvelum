<?php
class Backend_Designer_Sub_Storesubproperty extends Backend_Designer_Sub_Store
{

	public function __construct(){
		parent::__construct();
		$this->_checkObject();
	}
	
	public function proplistAction()
	{
		$proxyType = '';
		$writerType = '';
		$readerType = '';
		
		$proxy = $this->_object->proxy;
		
		if(!empty($proxy))
		{
			$class = explode('_', $proxy->getClass());
			$proxyType =  $class[(count($class) -1)];
				
			$reader = $this->_object->proxy->reader;
			if(!empty($reader)){
				$class = explode('_', $reader->getClass());
				$readerType =  $class[(count($class) -1)];
			}
			
			$writer = $this->_object->proxy->writer;
			if(!empty($writer)){
				$class = explode('_', $writer->getClass());
				$writerType =  $class[(count($class) -1)];
			}		
		}

		$results = array(
			array('name'=>'proxy','value'=>strtolower($proxyType)),
			array('name'=>'reader','value'=>strtolower($readerType)),
			array('name'=>'writer','value'=>strtolower($writerType)),
		);
		
		Response::jsonSuccess($results);
	}
	
	public function listAction()
	{
		$sub = Request::post('sub', 'string', '');
		if(!in_array($sub , array('proxy','reader','writer')))
			Response::jsonError($this->_lang->INVALID_REQUEST);	
			
		switch ($sub)
		{
			case 'proxy':		
					
				if($this->_object->proxy===''){
					$proxy = Ext_Factory::object('Data_Proxy_Ajax');
					$proxy->reader = Ext_Factory::object('Data_Reader_Json');
					$proxy->writer =  '';
					$this->_object->proxy = $proxy;		
					$this->_storeProject();
				}	
				
				$properties = $this->_object->proxy->getConfig()->__toArray();
				unset($properties['reader']);
				unset($properties['writer']);
				Response::jsonSuccess($properties);
			break;
			
			case 'reader':
			    if($this->_object->proxy->reader)
				    Response::jsonSuccess($this->_object->proxy->reader->getConfig()->__toArray());
			    else 
			        Response::jsonSuccess(array());
				break;
				
			case 'writer':
				 if(!isset($this->_object->proxy->writer) || empty($this->_object->proxy->writer))
				 	Response::jsonSuccess(array());
				 	
				 Response::jsonSuccess($this->_object->proxy->writer->getConfig()->__toArray());	
				break;
		}			
	}
	
	/**
	 * Change  subproprty object type
	 */
	public function changetypeAction()
	{
		$sub = Request::post('sub', 'string', '');
		$type = Request::post('type', 'string', '');
		
		if(!in_array($sub , array('proxy','reader','writer')) || !strlen($type))
			Response::jsonError($this->_lang->INVALID_REQUEST);
			
		$config = array();
		
		if($sub == 'proxy')			
			$obj = $this->_object->proxy;
		else	
			$obj = $this->_object->proxy->$sub;
			
		if(!empty($obj))
			$config = $obj->getConfig()->__toArray();
		
		if($sub == 'proxy')
		{
			$this->_object->proxy = Ext_Factory::object('Data_'.ucfirst($sub).'_' . ucfirst($type),$config);
			$this->_object->proxy->type = strtolower($type);
			
			if($this->_object->proxy->getClass()==='Data_Proxy_Ajax'){
					$this->_object->proxy->startParam='pager[start]';
			        $this->_object->proxy->limitParam='pager[limit]';
			        $this->_object->proxy->sortParam='pager[sort]';
			        $this->_object->proxy->directionParam='pager[dir]';
			        $this->_object->proxy->simpleSortMode= true;
			}			
		}
		else
		{		
			$this->_object->proxy->$sub = Ext_Factory::object('Data_'.ucfirst($sub).'_' . ucfirst($type),$config);
			$this->_object->proxy->$sub->type = strtolower($type);
			
			if($this->_object->proxy->getClass()==='Data_Reader_Json'){				
					$this->_object->proxy->$sub->rootProperty = 'data';
					$this->_object->proxy->$sub->totalProperty = 'count';
					$this->_object->proxy->$sub->idProperty = 'id';			
			}
		}
		$this->_storeProject();
		Response::jsonSuccess();
	}
	
	/**
	 * Set sub object property
	 */
	public function setpropertyAction()
	{
		$property = Request::post('name', 'raw', false);
		$value = Request::post('value', 'raw', false);
		$sub = Request::post('sub', 'string', '');
		
		
		if(!in_array($sub , array('proxy','reader','writer')) || !strlen($property))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		if($sub == 'proxy')			
			$obj = $this->_object->proxy;
		else	
			$obj = $this->_object->proxy->$sub;
			
		if(!$obj->isValidProperty($property))
			Response::jsonError();
			
		$obj->$property = $value;

		$this->_storeProject();
		Response::jsonSuccess();
	}
	
}
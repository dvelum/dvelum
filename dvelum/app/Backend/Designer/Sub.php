<?php
class Backend_Designer_Sub
{
	/**
	 * @var Lang
	 */
	protected $_lang;
	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db;
	/**
	 * Designer config
	 * @var Config_File_Array
	 */
	protected $_config;
	/**
	 * @var Store_Session
	 */
	protected $_session;
	/**
	 * @var Designer_Storage_Adapter_Abstract
	 */
	protected $_storage;
	/**
	 * @var Config_Abstract
	 */
	protected $_configMain;
	
	static protected $_poject = null;
	
	public function __construct()
	{
		$this->_configMain = Registry::get('main' , 'config');
		$this->_lang = Lang::lang();
		$this->_db = Model::getDefaultDbManager()->getDbConnection('default');
		$this->_config = Config::storage()->get('designer.php');
		$this->_session = Store_Session::getInstance('Designer');
		$this->_storage = Designer_Storage::getInstance($this->_config->get('storage') , $this->_config);
	}
	/**
	 * Check if project is loaded
	 */	
	protected function _checkLoaded()
	{
		if(!$this->_session->keyExists('loaded') || !$this->_session->get('loaded'))
			Response::jsonError($this->_lang->MSG_PROJECT_NOT_LOADED);	
	}
	/**
	 * Get project object from
	 * session storage
	 * @return Designer_Project
	 */
	protected function _getProject()
	{
		if(is_null(self::$_poject))
			self::$_poject = unserialize($this->_session->get('project'));	
		return self::$_poject;
	}
	/**
	 * Store project data
	 */
	protected function _storeProject()
	{
		$this->_session->set('project', serialize($this->_getProject()));
	}
	/**
	 * Check requested object
	 * Get requested object from project
	 * @return Ext_Object
	 */
	protected function _getObject()
	{
		$name = Request::post('object', 'string', '');
		$project = $this->_getProject();
		
		if(!strlen($name) || !$project->objectExists($name))
			Response::jsonError($this->_lang->WRONG_REQUEST);
	
		return $project->getObject($name);
	}
}
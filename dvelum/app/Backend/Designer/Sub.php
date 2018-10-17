<?php
class Backend_Designer_Sub
{
	/**
	 * @var Lang
	 */
	protected $_lang;
	/**
	 * @var Dvelum\Db\Adapter
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

    /**
     * @var \Dvelum\Response
     */
	protected $response;
    /**
     * @var Page
     */
	protected $page;
	
	public function __construct()
	{
		$this->_configMain = Config::storage()->get('main.php');
		$this->_lang = Lang::lang();
        /**
         * @var \Dvelum\Orm\Service $service
         */
		$service = \Dvelum\Service::get('orm');
		$this->_db = $service->getModelSettings()->get('defaultDbManager')->getDbConnection('default');

		$this->_config = Config::storage()->get('designer.php');
		$this->_session = Store_Session::getInstance('Designer');
		$this->_storage = Designer_Storage::getInstance($this->_config->get('storage') , $this->_config);

		$this->page =  \Page::getInstance();
		$this->response = \Dvelum\Response::factory();
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
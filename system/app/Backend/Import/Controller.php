<?php
class Backend_Import_Controller extends Backend_Controller
{
	/**
	 * @var Lang
	 */
	protected $_iLang = false;

    public function __construct()
    {
    	parent::__construct();
    	$langPath = $this->_configMain->get('lang_path') . $this->_configMain->get('language').'/import.php';
    	Lang::addDictionaryLoader('import', $langPath, Config::File_Array);
    	$this->_iLang = Lang::lang('import');

    }
    /**
     * (non-PHPdoc)
     * @see Backend_Controller::indexAction()
     */
     public function indexAction(){
        $res = Resource::getInstance();
	    $res->addJs('/js/app/system/ImportPanel.js'  , 0);
	    $res->addJs('/js/app/system/crud/import.js'  , 1);

	     $this->_resource->addInlineJs('
	        var importLang = '.$this->_iLang->getJson().';
            var canPublish =  '.((integer)$this->_user->canPublish($this->_module)).';
        	var canEdit = '.((integer)$this->_user->canEdit($this->_module)).';
        	var canDelete = '.((integer)$this->_user->canDelete($this->_module)).';
        ');
    }

    public function objectsAction()
    {
    	$manager = new Db_Object_Manager();
    	$objects = $manager->getRegisteredObjects();
    	$data = array();

    	if(!empty($objects))
    		foreach ($objects as $name)
    		$data[] = array('name'=>$name ,'title'=>Db_Object_Config::getInstance($name)->getTitle());

    	Response::jsonSuccess($data);
    }

    public function uploadAction()
    {
    	$object = Request::post('object', 'string', false);


    	if(!$object || !Db_Object_Config::configExists($object))
    		Response::jsonError($this->_lang->get('FILL_FORM'));



    	$cfg = Db_Object_Config::getInstance($object);
    	$fields = $cfg->getFieldsConfig(false);

    	$expectedCols = array();

    	$expectedCols[] = array(
    		'id'=>$cfg->getPrimaryKey(),
    		'text'=>$this->_lang->get('PRIMARY_KEY'),
    		'columnIndex'=>-1,
    	    'required'=>1
    	);

    	foreach ($fields as $name=>$fConfig)
    	{
    		$expectedCols[] = array(
    			'id'=>$name,
    			'text'=>$fConfig['title'],
    			'columnIndex'=>-1,
    		    'required' => $cfg->isRequired($name)
    		);
    	}

    	$result = array(
    		'success'=>true,
    		'expectedColumns' => $expectedCols,
    		'data'=>array(),
    		'uploadId'=>1
    	);

    	if(!$cfg->isSystem())
    		$result['importTypes'][] = array('name'=>'type' , 'inputValue'=>'rewrite', 'boxLabel'=>$this->_iLang->get('REWRITE'));


		//echo json_encode(array('success'=>false,'msg'=>'Cannot upload file')); exit;

		for($i=0;$i<30;$i++){

			$result['data'][] = array(
				'col0'=>'Data'.rand(0,6),
				'col1'=>rand(1000,9999),
				'col2'=>rand(1,700),
				'col3'=>rand(1,700),
				'col4'=>rand(1,700),
				'col5'=>rand(1,700),
				'col6'=>rand(1,700),
			);
		}
		$result['col_count'] = sizeof($result['data'][0]);
		Response::jsonArray($result);
    }

    public function importAction()
    {
    	$result = array(
    		'success'=>true,
    		'success_records'=>123
    	);

    	for($i=0;$i<10;$i++){

			$result['data'][] = array(
				'col0'=>'Data'.rand(0,6),
				'col1'=>rand(1000,9999),
				'col2'=>rand(1,700),
				'col3'=>rand(1,700),
				'col4'=>rand(1,700),
				'col5'=>rand(1,700),
				'col6'=>rand(1,700),
			);
    	}
        Response::jsonArray($result);
    }
}
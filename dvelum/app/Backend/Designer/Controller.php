<?php
class Backend_Designer_Controller extends Backend_Controller
{
	/**
	 * Designer config
	 * @var Config_File_Array
	 */
	protected $_config;
	protected $_version;
	
	
	static  protected $_externalScripts = array(
		/*
		 * External
		 */
		'/js/lib/CodeMirror/lib/codemirror.js', 

		'/js/lib/CodeMirror/addon/hint/show-hint.js',
		'/js/lib/CodeMirror/addon/hint/javascript-hint.js',
		'/js/lib/CodeMirror/addon/dialog/dialog.js',
		'/js/lib/CodeMirror/addon/search/search.js',
		'/js/lib/CodeMirror/addon/search/searchcursor.js',
		'/js/lib/CodeMirror/addon/search/match-highlighter.js',
		'/js/lib/CodeMirror/addon/selection/active-line.js',

		'/js/lib/CodeMirror/mode/javascript/javascript.js',


		'/js/lib/ext_ux/TreeCellEditing.js',
		//'/js/lib/ext_ux/RowExpander.js',
	);
	/**
	 * Source scripts
	 * @var array
	 */
	static protected $_scripts = array(	
	
		/*
		 * Internal
		 */
	 
		'/js/app/system/SearchPanel.js',
		'/js/app/system/designer/importDBWindow.js',
		'/js/app/system/designer/application.js',
		'/js/app/system/designer/urlField.js',
		'/js/app/system/designer/iconField.js',
		'/js/app/system/designer/ormSelectorWindow.js',	
		'/js/app/system/designer/iconSelectorWinow.js',
		'/js/app/system/designer/defaultsWindow.js',
	    '/js/app/system/designer/paramsWindow.js',
	        
		//'/js/app/system/designer/connections.js',
		
		'/js/app/system/designer/instanceWindow.js',
	    
	    '/js/app/system/designer/configWindow.js',
		'/js/app/system/designer/configWindow.js',
		'/js/app/system/designer/codeEditor.js',
		'/js/app/system/designer/eventsPanel.js',
	    '/js/app/system/designer/methodEditor.js',
	    '/js/app/system/designer/methodsPanel.js',
		'/js/app/system/designer/eventsEditor.js',
		'/js/app/system/designer/objects.js',
		'/js/app/system/designer/objects/tree.js',
		'/js/app/system/designer/objects/grid.js',
		'/js/app/system/designer/grid.js',	
		'/js/app/system/designer/grid/column.js',
		'/js/app/system/designer/grid/column/FilterWindow.js',
		'/js/app/system/designer/grid/column/ActionsWindow.js',
		'/js/app/system/designer/grid/column/RendererWindow.js',
		'/js/app/system/designer/grid/filters.js',
		
		'/js/app/system/designer/properties.js',
		'/js/app/system/designer/properties/grid.js',
		'/js/app/system/designer/properties/column.js',
		'/js/app/system/designer/properties/gridFilter.js',
		'/js/app/system/designer/properties/dataField.js',
		'/js/app/system/designer/properties/store.js',
		'/js/app/system/designer/properties/treeStore.js',
		'/js/app/system/designer/properties/model.js',
		'/js/app/system/designer/properties/field.js',
		'/js/app/system/designer/properties/window.js',
		'/js/app/system/designer/properties/crudWindow.js',
		'/js/app/system/designer/properties/form.js',
		'/js/app/system/designer/properties/gridEditor.js',
		'/js/app/system/designer/properties/filterComponent.js',
		'/js/app/system/designer/properties/search.js',
		'/js/app/system/designer/properties/mediaItem.js',
		
		'/js/app/system/designer/model.js',
		'/js/app/system/designer/store.js',
		'/js/app/system/designer/relatedProjectItemsWindow.js',
		
		'/js/app/system/FilesystemWindow.js',
		
		'/js/app/system/orm/connections.js',


		'/js/app/system/crud/designer.js',

	);
	
	
	public function __construct()
	{
		parent::__construct();
		$this->_config = Config::storage()->get('designer.php');
		$this->_version = Config::storage()->get('versions.php')->get('designer');
	}
	
	public function indexAction()
	{
        // change theme
        $designerTheme = $this->_config->get('theme');
        $this->_configBackend->set('theme' , $designerTheme);
        $page = Page::getInstance();
        $page->setTemplatesPath('system/' . $designerTheme. '/');


		$this->_resource->addJs('/js/lib/jquery.js'  , 1);
		Model::factory('Medialib')->includeScripts();
	    $this->_resource->addJs('/js/app/system/designer/lang/'.$this->_config->get('lang').'.js'  , 1);
		$this->_resource->addCss('/js/app/system/designer/style.css' );		
		$this->_resource->addCss('/js/lib/CodeMirror/lib/codemirror.css');
		$this->_resource->addCss('/js/lib/CodeMirror/addon/dialog/dialog.css');
		$this->_resource->addCss('/js/lib/CodeMirror/addon/hint/show-hint.css');
		$this->_resource->addCss('/js/lib/CodeMirror/theme/eclipse.css');
		
		
		$dbConfigs = array();
		foreach ($this->_configMain->get('db_configs') as $k=>$v){
		    $dbConfigs[]= array('id'=>$k , 'title'=>$this->_lang->get($v['title']));
		}
		
		$componentTemplates = Config::storage()->get('designer_templates.php')->__toArray();
		$this->_resource->addInlineJs('
		      var dbConfigsList = '.json_encode($dbConfigs).';    
		      var componentTemplates = ' . json_encode(array_values($componentTemplates)).';  
		');
		
		$count = 4;

		foreach (self::$_externalScripts as $path){
				$this->_resource->addJs($path , $count);
				$count++;
		}
		
		if(!$this->_config->get('development')){
			$this->_resource->addJs($this->_config->get('compiled_js').'?v='.$this->_version,$count);
		}else{
			foreach (self::$_scripts as $path){
				$this->_resource->addJs($path , $count);
				$count++;
			}
		}
		
	}
	
	public function subAction()
	{
		$subController = Request::getInstance()->getPart(3);
		$subAction = Request::getInstance()->getPart(4);
		if($subController === false || !strlen($subController) || $subAction===false || !strlen($subAction))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$subController = 'Backend_Designer_Sub_'.ucfirst(Filter::filterValue('pagecode',$subController));
		$subAction = Filter::filterValue('pagecode',$subAction).'Action';

		if(!class_exists($subController) || !method_exists($subController, $subAction))
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$sub = new $subController();
		$sub->$subAction();	
		
		exit();
	}
	
	/**
	 * Compilation of Layout Designer code
	 * System method used by platform developers
	 */
	public function compileAction()
	{
		if(!$this->_config->get('development')){
			die('Use development mode');
		}
		
		$s = '';
		$totalSize = 0;
		foreach (self::$_scripts as $filePath){
			$s.=file_get_contents($this->_configMain['wwwpath'].$filePath)."\n";
			$totalSize+=filesize($this->_configMain['wwwpath'].$filePath);
		}
		
		$time = microtime(true);	
		file_put_contents($this->_configMain['wwwpath'].$this->_config->get('compiled_js'), Code_Js_Minify::minify($s));
		
		echo '
			Compilation time: '.number_format(microtime(true)-$time,5).' sec<br>
			Files compiled: '.sizeof(self::$_scripts).' <br>
			Total size: '.Utils::formatFileSize($totalSize).'<br>
			Compiled File size: '.Utils::formatFileSize(filesize($this->_configMain['wwwpath'].$this->_config->get('compiled_js'))).' <br>
		';
		
		exit;
	}
	
	public function debuggerAction(){
	    
	    $subAction = Request::getInstance()->getPart(3);
	    if(!$subAction)
	        $subAction = 'index';
	    
	    $subAction.='Action';	    
	    $subController = 'Backend_Designer_Debugger';
	    
	    if(!method_exists($subController, $subAction))
	        Response::jsonError($this->_lang->WRONG_REQUEST);
	    
	    $sub = new $subController();
	    $sub->$subAction();
	    
	    exit();
	}
}
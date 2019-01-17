<?php
class Backend_Designer_Sub_Viewframe extends Backend_Designer_Sub
{
	public function indexAction()
	{
		if(!$this->_session->keyExists('loaded') || !$this->_session->get('loaded')){
			$this->response->error('Project is not loaded');
			exit;
		}

		$designerConfig = Config::storage()->get('designer.php');
        $backendConfig = Config::storage()->get('backend.php');

        //$adminTheme = $backendConfig->get('theme');
        // change theme
		$designerTheme = $designerConfig->get('application_theme');
        $backendConfig->set('theme' , $designerTheme);

		$this->page->setTemplatesPath('system/' . $designerTheme. '/');


		$res = \Dvelum\Resource::factory();
		$backendScripts = Config::storage()->get('js_inc_backend.php');
		if($backendScripts->getCount())
		{
			$js = $backendScripts->get('js');
			if(!empty($js))
				foreach($js as $file => $config)
					$res->addJs($file , $config['order'] , $config['minified']);

			$css = $backendScripts->get('css');
			if(!empty($css))
				foreach($css as $file => $config)
					$res->addCss($file , $config['order']);
		}

		Model::factory('Medialib')->includeScripts();

		$res->addJs('/js/app/system/SearchPanel.js');
		$res->addJs('/js/app/system/HistoryPanel.js', 0);
		//$res->addJs('/js/lib/ext_ux/RowExpander.js', 0);
		$res->addJs('/js/app/system/RevisionPanel.js', 1);
        $res->addJs('/js/app/system/EditWindow.js' , 2);
        $res->addJs('/js/app/system/ContentWindow.js' , 3);
		$res->addJs('/js/app/system/designer/viewframe/main.js',4);
		$res->addJs('/js/app/system/designer/lang/'.$designerConfig['lang'].'.js',5);

		$project = $this->_getProject();
		$projectCfg = $project->getConfig();

		Ext_Code::setRunNamespace($projectCfg['runnamespace']);
		Ext_Code::setNamespace($projectCfg['namespace']);

		$grids = $project->getGrids();

		if(!empty($grids))
		{
			foreach ($grids as $name=>$object)
			{
				if($object->isInstance())
				  continue;

				$cols = $object->getColumns();
				if(!empty($cols))
					foreach($cols as $column)
						$column['data']->projectColId = $column['id'];

				$object->addListener('columnresize','{
                     fn:function( ct, column, width,eOpts){
                        app.application.onGridColumnResize("'.$name.'", ct, column, width, eOpts);
                     }
				}');

				$object->addListener('columnmove','{
                    fn:function(ct, column, fromIdx, toIdx, eOpts){
                        app.application.onGridColumnMove("'.$name.'", ct, column, fromIdx, toIdx, eOpts);
                    }
				}');
			}
		}

		$dManager = Dictionary_Manager::factory();
		$key = 'vf_'.md5($dManager->getDataHash().serialize($project));

		$templates = $designerConfig->get('templates');
    	$replaces = array(
    			array('tpl'=>$templates['wwwroot'],'value'=>$this->_configMain->get('wwwroot')),
    			array('tpl'=>$templates['adminpath'],'value'=>$this->_configMain->get('adminPath')),
    			array('tpl'=>$templates['urldelimiter'],'value'=>$this->_configMain->get('urlDelimiter')),
    	);

    	$includes = Designer_Factory::getProjectIncludes($key, $project , true , $replaces , true);

	   if(!empty($includes))
		{
			foreach ($includes as $file)
			{
	            if(File::getExt($file) == '.css')
			       $res->addCss($file , 100);
			    else
				   $res->addJs($file , false, false);
			}
		}

		$names = $project->getRootPanels();

		$basePaths = array();

		$parts = explode('/', $this->_configMain->get('wwwroot'));
		if(is_array($parts) && !empty($parts)){
			foreach ($parts as $item){
				if(!empty($item)){
				    $basePaths[] = $item;
				}
			}
		}

		$basePaths[] = $this->_configMain['adminPath'];
		$basePaths[] = 'designer';
		$basePaths[] = 'sub';

		//' . $project->getCode($replaces) . '
		$initCode = '
		app.delimiter = "'.$this->_configMain['urlDelimiter'].'";
		app.admin = "' . $this->_configMain->get('wwwroot') . $this->_configMain->get('adminPath').'";
		app.wwwRoot = "' . $this->_configMain->get('wwwroot') . '";

		var applicationClassesNamespace = "'.$projectCfg['namespace'].'";
		var applicationRunNamespace = "'.$projectCfg['runnamespace'].'";
		var designerUrlPaths = ["'.implode('","', $basePaths).'"];

		var canDelete = true;
		var canPublish = true;
		var canEdit = true;

		app.permissions = Ext.create("app.PermissionsStorage");
		var rights = '.json_encode(User::getInstance()->getPermissions()).';
		app.permissions.setData(rights);

		Ext.onReady(function(){
		    app.application.mainUrl = app.createUrl(designerUrlPaths);
            ';

		if(!empty($names))
		{
			foreach ($names as $name)
			{
				if($project->getObject($name)->isExtendedComponent()){

					/*if($project->getObject($name)->getConfig()->defineOnly)
						continue;
					*/
					$initCode.= Ext_Code::appendRunNamespace($name).' = Ext.create("'.Ext_Code::appendNamespace($name).'",{});';
				}
				$initCode.='
			        app.viewFrame.add('.Ext_Code::appendRunNamespace($name).');
			    ';
			}
		}

	   $initCode.='
        	 app.application.fireEvent("projectLoaded");
	   });';

		$res->addInlineJs($initCode);

		$backendConfig = Config::storage()->get('backend.php');
		$tpl = \Dvelum\View::factory();
		$tpl->lang = $this->_configMain['language'];
		$tpl->development = $this->_configMain['development'];
		$tpl->resource = $res;
		$tpl->useCSRFToken = $backendConfig->get('use_csrf_token');
		$tpl->theme = $designerTheme;

		$this->response->put($tpl->render($this->page->getTemplatesPath().'designer/viewframe.php'));
	    $this->response->send();
	}
}
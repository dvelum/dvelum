<?php
class Backend_Designer_Sub_Actionjs extends Backend_Designer_Sub
{
	/**
	 * Get ActionJs Code
	 */
	public function loadAction()
	{
		$project = $this->_getProject();
		$actionjs = $project->actionjs;
		$actionjs = str_replace(array('../'), '', $actionjs);

		if($actionjs[0]=='/')
			$actionjs = './'.$actionjs;

		$actionjsFile = $this->_configMain->get('wwwpath').$actionjs;

		if(!file_exists($actionjsFile))
		{
			$fileDir = dirname($actionjsFile);
			if(!file_exists($fileDir))
			{
				if(!@mkdir($fileDir,0775,true))
					Response::jsonError($this->_lang->CANT_WRITE_FS);	
			}
			$tpl = new Template();
			if(!@file_put_contents($actionjsFile, $tpl->render(Application::getTemplatesPath().'designer/emptyaction.php')))
				Response::jsonError($this->_lang->CANT_WRITE_FS);
				
			$project->actionjs = $actionjs;
			$this->_storeProject();
		}
		
		Response::jsonSuccess(file_get_contents($actionjsFile));
	}
	/**
	 * Save ActionJs code
	 */
	public function saveAction()
	{
		$code = Request::post('code', 'raw', false);
		
		if($code === false)
			Response::jsonError($this->_lang->WRONG_REQUEST);
			
		$project = $this->_getProject();	
		$actionjs = str_replace('../', '', $project->actionjs);

		if($actionjs[0]=='/')
			$actionjs = './'.$actionjs;

		$actionjsFile = $this->_configMain->get('wwwpath').$actionjs;

		
		if(!@file_put_contents($actionjsFile, $code))
			Response::jsonError($this->_lang->CANT_WRITE_FS .' ' . $actionjs);	
		
		Response::jsonSuccess();		
	}
}
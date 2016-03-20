<?php
class Backend_Designer_Sub_Actionjs extends Backend_Designer_Sub
{
	/**
	 * Get ActionJs Code
	 */
	public function loadAction()
	{
		$project = $this->_getProject();
		Response::jsonSuccess($project->getActionJs());
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
		$project->setActionJs($code);
		$this->_storeProject();
		Response::jsonSuccess();		
	}
}
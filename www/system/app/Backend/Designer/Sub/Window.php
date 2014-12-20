<?php
class Backend_Designer_Sub_Window extends Backend_Designer_Sub
{
	/**
	 * Change window size
	 */
	public function changesizeAction()
	{	
		$object = Request::post('object', 'string', false);
		$width =  Request::post('width', 'integer', false);
		$height =  Request::post('height', 'integer', false);
		
		if($object===false || $width===false || $height===false)
			Response::jsonError($this->_lang->WRONG_REQUEST . 'code 1');
			
		$project = $this->_getProject();
		if(!$project->objectExists($object))	
			Response::jsonError($this->_lang->WRONG_REQUEST . 'code 2');
			
		$object = $project->getObject($object);
		$object->width = $width;
		$object->height = $height;

		$this->_storeProject();
		Response::jsonSuccess();
	}
}
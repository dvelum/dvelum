<?php
class Backend_Designer_Sub_Grid extends Backend_Designer_Sub
{
	/**
	 * @var Designer_Project
	 */
	protected $_project;
	/**
	 * @var Ext_Grid
	 */
	protected $_object;

	public function __construct(){
		parent::__construct();
		$this->_checkLoaded();
		$this->_checkObject();
	}

	protected function _checkObject()
	{
		$name = Request::post('object', 'string', '');
		$project = $this->_getProject();
		if(!strlen($name) || !$project->objectExists($name) || $project->getObject($name)->getClass()!=='Grid')
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$this->_project = $project;
		$this->_object = $project->getObject($name);
	}
	/**
	 * Get grid columns as tree list
	 */
	public function columnlisttreeAction()
	{
		Response::jsonArray($this->_object->getColumnsList());
	}

	/**
	 * Get grid columns as simple list
	 */
	public function columnlistAction()
	{
		$cols = $this->_object->getColumns();
		$result = array();

		if(!empty($cols))
		{
			foreach ($cols as $name=>$data)
			{
				$object = $data['data'];
				$type = '';
				$className = $object->getClass();
				if($className !== 'Grid_Column')
					$type = strtolower(str_replace('Grid_Column_', '', $className));

				$editor = '';
				if(is_a($object->editor, 'Ext_Object')){
					$editor = $object->editor->getClass();
				}

				$filter = '';
				if(!empty($object->filter) && $object->filter instanceof Ext_Grid_Filter) {
					$filter = $object->filter->getType();
				}

				$result[] = array(
					'id'=>$name,
					'text'=>$object->text,
					'dataIndex'=>$object->dataIndex,
					'type'=>$type,
					'editor'=>$editor,
					'filter'=>$filter,
					'order'=>$data['order']
				);
			}
		}
		Response::jsonSuccess($result);
	}
	/**
	 * Sort grid columns
	 */
	public function columnsortAction()
	{
        $id = Request::post('id','string',false);
        $newParent = Request::post('newparent','string',false);
        if(!strlen($newParent))
        	$newParent = 0;
        $order = Request::post('order', 'array' , array());

        if(!$id  || !$this->_object->columnExists($id))
            Response::jsonError($this->_lang->WRONG_REQUEST .' code1');

		$this->_object->changeParent($id, $newParent);
        $count = 0;
        foreach ($order as $name)
        {
        	if(!$this->_object->setItemOrder($name, $count))
        		Response::jsonError($this->_lang->WRONG_REQUEST.' code2');

        	$count ++;
        }
        $this->_object->reindexColumns();
        $this->_storeProject();
        Response::jsonSuccess();
	}
	/**
	 * Add grid column
	 */
	public function addcolumnAction()
	{
		$colId = Request::post('id','pagecode', '');

		if(!strlen($colId))
			Response::jsonError($this->_lang->INVALID_VALUE);

		if($this->_object->columnExists($colId))
			Response::jsonError($this->_lang->SB_UNIQUE);

		$column = Ext_Factory::object('Grid_Column');
		$column->text = $colId;
		$column->itemId = $colId;
		$column->setName($colId);

		if(!$this->_object->addColumn($colId,$column,0))
			Response::jsonError($this->_lang->INVALID_VALUE);

		$this->_storeProject();
		Response::jsonSuccess();
	}
	/**
	 * Remove grid column
	 */
	public function removecolumnAction()
	{
		$colId = Request::post('id','string', '');
		if(!strlen($colId))
			Response::jsonError($this->_lang->INVALID_VALUE . 'code 1');

		if(!$this->_object->columnExists($colId))
			Response::jsonError($this->_lang->INVALID_VALUE . 'code 2');

		$this->_object->removeColumn($colId);
		$this->_storeProject();
		Response::jsonSuccess();
	}
	/**
	 * Add columns
	 */
	public function addcolumnsAction()
	{
		$columns = Request::post('col','raw',false);
		if(empty($columns))
			Response::jsonError($this->_lang->INVALID_VALUE);
		$columns = json_decode($columns,true);

		foreach ($columns as $v)
		{
			if($this->_object->columnExists($v['name']))
				Response::jsonError($this->_lang->SB_UNIQUE);


			switch ($v['type']){
				case 'boolean':	$column = Ext_Factory::object('Grid_Column_Boolean');
					break;
				case 'integer':
				case 'float':	$column = Ext_Factory::object('Grid_Column_Number');
					break;
				case 'date': 	$column = Ext_Factory::object('Grid_Column_Date');
					break;
				default:		$column = Ext_Factory::object('Grid_Column');
			}

			$column->text = $v['name'];
			$column->dataIndex = $v['name'];
			$column->setName($v['name']);

			if(!$this->_object->addColumn($v['name'],$column,0))
				Response::jsonError($this->_lang->INVALID_VALUE);
		}
		$this->_storeProject();
		Response::jsonSuccess();
	}
	/**
	 * Change grid column type
	 */
	public function changecoltypeAction(){
		$type = Request::post('type', 'string', '');
		$columnId = Request::post('columnId','string',false);

		if(!$columnId)
			Response::jsonError($this->_lang->WRONG_REQUEST);

		if(strlen($type))
			$name = 'Grid_Column_'.ucfirst($type);
		else
			$name = 'Grid_Column';

		$col = Ext_Factory::object($name);

		Ext_Factory::copyProperties($this->_object->getColumn($columnId), $col);

		if(!$this->_object->updateColumn($columnId, $col))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$this->_storeProject();
		Response::jsonSuccess();
	}
	/**
	 * Save advnced properties
	 */
	public function setadvancedAction()
	{
		$errors = array();

		foreach (Ext_Grid::$advancedProperties as $key=>$type)
		{
			$value = Request::post($key, $type, '');
			if(!$this->_object->setAdvancedProperty($key, $value))
				$errors[$key]=$this->_lang->INVALID_VALUE;
		}

		if(empty($errors))
		{
			$this->_storeProject();
			Response::jsonSuccess();
		}
		else
		{
			Response::jsonError($this->_lang->INVALID_VALUE ,$errors);
		}
	}
	/**
	 * Get advanced properties for grid object
	 */
	public function loadadvancedAction()
	{
		Response::jsonSuccess($this->_object->getAdvancedProperties());
	}
}
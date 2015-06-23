<?php
class Backend_Orm_Dataview extends Backend_Controller
{
	public function getModule(){
		return 'Orm';
	}
	public function indexAction(){}

	public function viewconfigAction()
	{
		$object = Request::post('object', 'string', false);

		if(!$object || !Db_Object_Config::configExists($object))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$cfg = Db_Object_Config::getInstance($object);

		$fields = $cfg->getFieldsConfig(true);
		$fieldsCfg = array();
		$columns = array();
		$systemcolumns = array();
		$searchfields = $cfg->getSearchFields();

		foreach ($fields as $name=>$itemCfg)
		{
			$fieldCfg = new stdClass();
			$fieldCfg->name = $name;

			$col = new stdClass();
			$col->dataIndex = $name;
			$col->text = $itemCfg['title'];

			$dbType = $cfg->getDbType($name);

			if($cfg->isLink($name))
			{
				$col->align = 'left';
				$fieldCfg->type = "string";

				if($cfg->isDictionaryLink($name)){
					$col->text.=' <span style="color:green;">('.$this->_lang->DICTIONARY.')</span>';
				}elseif($cfg->isMultiLink($name)){
					$col->text.=' <span style="color:red;">('.$this->_lang->MULTI_LINK.')</span>';
				}else{
					$col->text.=' <span style="color:#0000FF;">('.$this->_lang->LINK.')</span>';
				}


			}
			elseif($cfg->isNumeric($name))
			{
				$col->align = 'right';
				if($cfg->isFloat($name)){
					$fieldCfg->type = "float";
					$col->xtype="numbercolumn";
				}
				else{
					$fieldCfg->type = "integer";
				}
			}
			elseif($cfg->isBoolean($name))
			{
				$fieldCfg->type = "boolean";
				$col->xtype="booleancolumn";
				$col->align = 'center';
			}
			elseif($cfg->isText($name))
			{
				$col->align = 'left';
				$fieldCfg->type = "string";
			}
			elseif($dbType == 'date' || $dbType == 'datetime')
			{
				$col->xtype = 'datecolumn';
				$fieldCfg->type = "date";
				if($dbType == 'date')
				{
					$col->format = 'd.m.Y';
					$fieldCfg->dateFormat = 'Y-m-d';
				}
				else
				{
					$col->format = 'd.m.Y H:i:s';
					$fieldCfg->dateFormat = 'Y-m-d H:i:s';
				}
			}
			else
			{
				$col->align = 'left';
				$fieldCfg->type = "string";
			}

			if($cfg->isSearch($name))
			{
				  $col->text='<img data-qtip="'.$this->_lang->SEARCH.'" src="'.$this->_configMain->get('wwwroot').'i/system/search.png" height="10"/> ' .$col->text;
			}

			if(isset($itemCfg['system']) && $itemCfg['system'])
				$systemcolumns[]= $col;
			else
				$columns[] = $col;

			$fieldsCfg[] = $fieldCfg;
		}

		$columns = array_merge($systemcolumns , $columns);

		$result = array(
			'fields' => $fieldsCfg,
			'columns' => $columns,
			'searchFields' => $searchfields
		);

		Response::jsonSuccess($result);
	}

	public function listAction()
	{
		$object = Request::post('object', 'string', false);
		$query = Request::post('search', 'string', false);
		$params = Request::post('pager', 'array', array());

		if(!$object || !Db_Object_Config::configExists($object))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$cfg = Db_Object_Config::getInstance($object);
		$fieldsCfg = $cfg->getFieldsConfig(true);

		$fields = array();
		$dictionaries = array();
		$objectLinks = $cfg->getLinks(array(Db_Object_Config::LINK_OBJECT));
		$multylinks = $cfg->getLinks(array(Db_Object_Config::LINK_OBJECT_LIST));

		foreach ($fieldsCfg as $name=>$fCfg)
		{
			if($cfg->isDictionaryLink($name))
			{
				$dictionaries[$name] = $cfg->getLinkedDictionary($name);
			}

			if($cfg->isText($name) && !$cfg->isLink($name))
			{
				$fields[$name] = '"[ text ]"';
			}
			else
			{
				$fields[] = $name;
			}
		}

		$model = Model::factory($object);
		$count = $model->getCount(false , $query , false);
		$data = array();

		if($count)
		{
			$data = $model->getList($params , false , $fields , false , $query);
			if(!empty($objectLinks))
			{
				$objectIds = array();
				foreach ($data as $row)
				{
					foreach ($objectLinks as $obj=>$fields)
					{
						if(!isset($objectIds[$obj]))
							$objectIds[$obj] = array();

						foreach ($fields as $fName=>$lType)
							$objectIds[$obj][] = $row[$fName];

					}
				}

				foreach ($objectIds as $oName=>&$idsList)
				{
					if(empty($idsList))
						continue;

					array_unique($idsList);
					$oCfg = Db_Object_Config::getInstance($oName);
					$linkedObjects = Db_Object::factory($oName , $idsList);

					$titleList = array();
					if(!empty($linkedObjects))
						foreach($linkedObjects as $id=>$item)
							$titleList[$id] = array('id'=>$id , 'title' =>$item->getTitle());

					$idsList = $titleList;
				}
			}

			if(!empty($dictionaries) || !empty($objectLinks) || !empty($multylinks))
			{
				foreach ($data as &$row)
				{
					if(!empty($dictionaries))
					{
						foreach ($dictionaries as $col=>$dictName)
						{
							$dictionary = Dictionary::getInstance($dictName);
							if($dictionary->isValidKey($row[$col]))
								$row[$col] = '['.$row[$col].'] '.$dictionary->getValue($row[$col]);
							else
								$row[$col] = '['.$row[$col].']';
						}
					}

					if(!empty($objectLinks))
					{
						foreach ($objectLinks as $object=>$fields)
						{
							foreach ($fields as $name=>$type)
							{
								if(isset($objectIds[$object][$row[$name]]))
									$row[$name] = '['.$objectIds[$object][$row[$name]]['id'].'] '.$objectIds[$object][$row[$name]]['title'];
							}
						}
					}

					if(!empty($multylinks))
					{
						foreach ($multylinks as $obj=>$fCfg)
						{
							foreach ($fCfg as $name=>$type)
							{
								$list = array();
								$rec = $row[$name];
								if(strlen($rec))
								{
									$rec = @unserialize($rec);
									if(!empty($rec))
										foreach ($rec as $item)
											$list[]='['.$item['id'].'] '.$item['title'];
								}
								$row[$name] = implode(', ', $list);
							}
						}
					}
				}
			}
		}

		Response::jsonSuccess($data , array('count'=>$count));
	}


	public function editorconfigAction()
	{
		$object = Request::post('object', 'string', false);
		if(!$object || !Db_Object_Config::configExists($object))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$objectConfig = Db_Object_Config::getInstance($object);

		$data = array();

		$tabs = array();

		$tab = Ext_Factory::object('Panel');
		$tab->setName('_generalTab');
		$tab->frame=false;
		$tab->border=false;
		$tab->layout='anchor';
		$tab->bodyPadding=3;
		$tab->scrollable = true;
		$tab->bodyCls='formBody';
		$tab->anchor= '100%';
		$tab->title = $this->_lang->get('GENERAL');
		$tab->fieldDefaults="{labelAlign: \"right\",labelWidth: 160,anchor: \"100%\"}";

		$tabs[] = $tab;
		$related = array();
		$objectFieldList = array_keys($objectConfig->getFieldsConfig(false));

		$backendCfg = Registry::get('main','config');


		$readOnly = $objectConfig->isReadOnly();

		foreach ($objectFieldList as $field)
		{
			if(is_string($field) && $field == 'id')
				continue;

			$fieldCfg = $objectConfig->getFieldConfig($field);

			if($objectConfig->isMultiLink($field))
			{
			  $linkedObject = $objectConfig->getLinkedObject($field);
			  $linkedCfg = Db_Object_Config::getInstance($linkedObject);
				$related[] = array(
					'field' => $field,
					'object' => $linkedObject,
					'title' => $fieldCfg['title'],
					'titleField' => $linkedCfg->getLinkTitle()
				);
			}
			elseif($objectConfig->isObjectLink($field))
			{
				if($objectConfig->getLinkedObject($field) === 'medialib')
				{
					$data[] ='{
									xtype:"medialibitemfield",
									resourceType:"all",
									name:"'.$field.'",
									readOnly:'.intval($readOnly).',
									fieldLabel:"'.$fieldCfg['title'].'"
								}';
				}
				else
				{

					$data[]='
						{
							xtype:"objectfield",
							objectName:"'.$objectConfig->getLinkedObject($field).'",
							controllerUrl:"'.Request::url(array($backendCfg['adminPath'] ,'orm' , 'dataview',''), false).'",
							fieldLabel:"'.$fieldCfg['title'].'",
							name:"'.$field.'",
							anchor:"100%",
							readOnly:'.intval($readOnly).',
							isVc:'.intval($objectConfig->isRevControl()).'
						}
					';
				}
			}
			else
			{
				$newField = Backend_Designer_Import::convertOrmFieldToExtField($field , $fieldCfg);

				if($newField!==false)
				{
					$newField->setName($field);
					$fieldClass = $newField->getClass();

					if($readOnly && $newField->getConfig()->isValidProperty('readOnly')){
					  $newField->readOnly = true;
					}

					if($objectConfig->isText($field) && $objectConfig->isHtml($field))
					{
						$tabs[] = $newField->__toString();
					}
					else
					{
						$data[] = $newField->__toString();
					}
				}
			}
		}

		$tab->items = '['.implode(',', $data).']';

		Response::jsonSuccess(array(
		'related'=>$related ,
		'fields'=>str_replace(array("\n","\t"),  '',  '['.implode(',' ,$tabs).']'),
		'readOnly'=>intval($readOnly),
		'primaryKey'=>$objectConfig->getPrimaryKey()
		));
	}

	public function editorAction()
	{
		$request = Request::getInstance();
		$router = new Backend_Router();
		$router->runController('Backend_Orm_Dataview_Editor' , $request->getPart(4));
	}

	public function editorvcAction()
	{
		$request = Request::getInstance();
		$router = new Backend_Router();
		$router->runController('Backend_Orm_Dataview_Editor_Vc' , $request->getPart(4));
	}

	public function otitleAction()
	{
		$object = Request::post('object','string', false);
		$id = Request::post('id', 'string', false);

		if(!$object || !Db_Object_Config::configExists($object))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		try {
			$o = Db_Object::factory($object, $id);
			Response::jsonSuccess(array('title'=>$o->getTitle()));
		}catch (Exception $e){
			Model::factory($object)->logError('Cannot get title for '.$object.':'.$id);
			Response::jsonError($this->_lang->get('CANT_EXEC'));
		}
	}
}
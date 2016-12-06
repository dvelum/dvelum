<?php
use Dvelum\Orm;

class Backend_Orm_Dataview extends Backend_Controller_Crud
{
	public function getModule(){
		return 'Orm';
	}
	public function indexAction(){}

	public function viewconfigAction()
	{
		$object = Request::post('object', 'string', false);

		if(!$object || !Orm\Object\Config::configExists($object))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$cfg = Orm\Object\Config::factory($object);

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

            $field = $cfg->getField($name);
			$dbType = $field->getDbType();

			if($field->isLink())
			{
				$col->align = 'left';
				$fieldCfg->type = "string";

				if($field->isDictionaryLink()){
					$col->text.=' <span style="color:green;">('.$this->_lang->DICTIONARY.')</span>';
				}elseif($field->isMultiLink()){
					$col->text.=' <span style="color:red;">('.$this->_lang->MULTI_LINK.')</span>';
				}else{
					$col->text.=' <span style="color:#0000FF;">('.$this->_lang->LINK.')</span>';
				}


			}
			elseif($field->isNumeric())
			{
				$col->align = 'right';
				if($field->isFloat()){
					$fieldCfg->type = "float";
					$col->xtype="numbercolumn";
				}
				else{
					$fieldCfg->type = "integer";
				}
			}
			elseif($field->isBoolean())
			{
				$fieldCfg->type = "boolean";
				$col->xtype="booleancolumn";
				$col->align = 'center';
			}
			elseif($field->isText())
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

			if($field->isSearch())
			{
				  $col->text='<img data-qtip="'.$this->_lang->SEARCH.'" src="'.$this->_configMain->get('wwwroot').'i/system/search.png" height="10"/> ' .$col->text;
			}

            if($field->isMultiLink()){
                $col->sortable = false;
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

		if(!$object || !Orm\Object\Config::configExists($object))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$cfg = Orm\Object\Config::factory($object);
		$fieldsCfg = $cfg->getFieldsConfig(true);

		$fields = array();
		$dictionaries = array();
		$objectLinks = $cfg->getLinks(array(Orm\Object\Config::LINK_OBJECT));
		$linkLists = $cfg->getLinks(array(Orm\Object\Config::LINK_OBJECT_LIST));

		foreach ($fieldsCfg as $name=>$fCfg)
		{
		    $field = $cfg->getField($name);

			if($field->isDictionaryLink())
			{
				$dictionaries[$name] = $field->getLinkedDictionary();
			}

			if($field->isText($name) && !$field->isLink())
			{
				$fields[$name] = '"[ text ]"';
			}
			elseif(!$field->isVirtual())
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

            $fieldsToShow = array_keys($cfg->getLinks(
                [
                    Orm\Object\Config::LINK_OBJECT,
                    Orm\Object\Config::LINK_OBJECT_LIST,
                    Orm\Object\Config::LINK_DICTIONARY
                ],
                false
            ));

            if(!empty($fieldsToShow))
                $this->addLinkedInfo($cfg, $fieldsToShow, $data, $cfg->getPrimaryKey());
		}

		Response::jsonSuccess($data , array('count'=>$count));
	}

	public function editorconfigAction()
	{
		$object = Request::post('object', 'string', false);
		if(!$object || !Orm\Object\Config::configExists($object))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$objectConfig = Orm\Object\Config::factory($object);

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

            $fieldObj = $objectConfig->getField($field);

			if($fieldObj->isMultiLink())
			{
			  $linkedObject = $fieldObj->getLinkedObject();
			  $linkedCfg = Orm\Object\Config::factory($linkedObject);
				$related[] = array(
					'field' => $field,
					'object' => $linkedObject,
					'title' => $fieldCfg['title'],
					'titleField' => $linkedCfg->getLinkTitle()
				);
			}
			elseif($fieldObj->isObjectLink())
			{
				if($fieldObj->getLinkedObject() === 'medialib')
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
							objectName:"'.$fieldObj->getLinkedObject().'",
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

					if($fieldObj->isText() && $fieldObj->isHtml())
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

		if(!$object || !Orm\Object\Config::configExists($object))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		try {
			$o = Orm\Object::factory($object, $id);
			Response::jsonSuccess(array('title'=>$o->getTitle()));
		}catch (Exception $e){
			Model::factory($object)->logError('Cannot get title for '.$object.':'.$id);
			Response::jsonError($this->_lang->get('CANT_EXEC'));
		}
	}
}
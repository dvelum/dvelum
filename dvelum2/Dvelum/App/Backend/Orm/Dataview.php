<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Dvelum\App\Backend\Orm;

use Dvelum\Service;
use Dvelum\Orm;
use Dvelum\Orm\Object\Config as ObjCfg;

class Dataview extends \Dvelum\App\Backend\Api\Controller{
    public function getModule() : string{
        return 'Orm';
    }

    public function indexAction(){
    }

    public function viewconfigAction(){
        $object = $this->request->post('object', 'string', false);
        if(!$object || !Service::get('orm')->configExists($object))
            $this->response->error($this->lang->WRONG_REQUEST);

        $cfg = ObjCfg::factory($object);

        $fields = $cfg->getFieldsConfig(true);
        $fieldsCfg = array();
        $columns = array();
        $systemcolumns = array();
        $searchfields = $cfg->getSearchFields();

        foreach($fields as $name => $itemCfg){
            $fieldCfg = new \stdClass();
            $fieldCfg->name = $name;

            $col = new \stdClass();
            $col->dataIndex = $name;
            $col->text = $itemCfg['title'];

            $field = $cfg->getField($name);
            $dbType = $field->getDbType();

            if($field->isLink()){
                $col->align = 'left';
                $fieldCfg->type = "string";

                if($field->isDictionaryLink()){
                    $col->text .= ' <span style="color:green;">('.$this->lang->DICTIONARY.')</span>';
                }elseif($field->isMultiLink()){
                    $col->text .= ' <span style="color:red;">('.$this->lang->MULTI_LINK.')</span>';
                }else{
                    $col->text .= ' <span style="color:#0000FF;">('.$this->lang->LINK.')</span>';
                }


            }elseif($field->isNumeric()){
                $col->align = 'right';
                if($field->isFloat()){
                    $fieldCfg->type = "float";
                    $col->xtype = "numbercolumn";
                }else{
                    $fieldCfg->type = "integer";
                }
            }elseif($field->isBoolean()){
                $fieldCfg->type = "boolean";
                $col->xtype = "booleancolumn";
                $col->align = 'center';
            }elseif($field->isText()){
                $col->align = 'left';
                $fieldCfg->type = "string";
            }elseif($dbType == 'date' || $dbType == 'datetime'){
                $col->xtype = 'datecolumn';
                $fieldCfg->type = "date";
                if($dbType == 'date'){
                    $col->format = 'd.m.Y';
                    $fieldCfg->dateFormat = 'Y-m-d';
                }else{
                    $col->format = 'd.m.Y H:i:s';
                    $fieldCfg->dateFormat = 'Y-m-d H:i:s';
                }
            }else{
                $col->align = 'left';
                $fieldCfg->type = "string";
            }

            if($field->isSearch()){
                $col->text = '<img data-qtip="'.$this->lang->SEARCH.'" src="'.$this->appConfig->get('wwwroot').'i/system/search.png" height="10"/> '.$col->text;
            }

            if($field->isMultiLink()){
                $col->sortable = false;
            }

            if(isset($itemCfg['system']) && $itemCfg['system'])
                $systemcolumns[] = $col;
            else
                $columns[] = $col;

            $fieldsCfg[] = $fieldCfg;
        }

        $columns = array_merge($systemcolumns, $columns);

        $result = array(
            'fields' => $fieldsCfg,
            'columns' => $columns,
            'searchFields' => $searchfields
        );

        $this->response->success($result);
    }

    public function listAction(){
        $object = $this->request->post('object', 'string', null);
        $query = $this->request->post('search', 'string', null);
        $params = $this->request->post('pager', 'array', null);

        if(!$object || !Service::get('orm')->configExists($object)){
            $this->response->error($this->lang->WRONG_REQUEST);
            return;
        }

        $cfg = ObjCfg::factory($object);
        $fieldsCfg = $cfg->getFieldsConfig(true);

        $fields = array();
        $dictionaries = array();

        foreach($fieldsCfg as $name => $fCfg){
            $field = $cfg->getField($name);

            if($field->isDictionaryLink()){
                $dictionaries[$name] = $field->getLinkedDictionary();
            }

            if($field->isText($name) && !$field->isLink()){
                $fields[$name] = '"[ text ]"';
            }elseif(!$field->isVirtual()){
                $fields[] = $name;
            }
        }

        $model = \Model::factory($object);
        $count = $model->query()->search($query)->getCount();
        $data = array();

        if($count){
            $data = $model->query()->params($params)->fields($fields)->search($query)->fetchAll();

            $fieldsToShow = array_keys($cfg->getLinks(
                [
                    ObjCfg::LINK_OBJECT,
                    ObjCfg::LINK_OBJECT_LIST,
                    ObjCfg::LINK_DICTIONARY
                ],
                false
            ));

            if(!empty($fieldsToShow))
                $this->addLinkedInfo($cfg, $fieldsToShow, $data, $cfg->getPrimaryKey());
        }

        $this->response->success($data, array('count' => $count));
    }

    public function editorconfigAction(){
        $object = $this->request->post('object', 'string', false);
        if(!$object || !Service::get('orm')->configExists($object)){
            $this->response->error($this->lang->WRONG_REQUEST);
            return;
        }

        $objectConfig = ObjCfg::factory($object);

        $data = array();

        $tabs = array();

        $tab = \Ext_Factory::object('Panel');
        $tab->setName('_generalTab');
        $tab->frame = false;
        $tab->border = false;
        $tab->layout = 'anchor';
        $tab->bodyPadding = 3;
        $tab->scrollable = true;
        $tab->bodyCls = 'formBody';
        $tab->anchor = '100%';
        $tab->title = $this->lang->get('GENERAL');
        $tab->fieldDefaults = "{labelAlign: \"right\",labelWidth: 160,anchor: \"100%\"}";

        $tabs[] = $tab;
        $related = array();
        $objectFieldList = array_keys($objectConfig->getFieldsConfig(false));

        $readOnly = $objectConfig->isReadOnly();

        foreach($objectFieldList as $field){
            if(is_string($field) && $field == 'id')
                continue;

            $fieldCfg = $objectConfig->getFieldConfig($field);

            $fieldObj = $objectConfig->getField($field);

            if($fieldObj->isMultiLink()){
                $linkedObject = $fieldObj->getLinkedObject();
                $linkedCfg = ObjCfg::factory($linkedObject);
                $related[] = array(
                    'field' => $field,
                    'object' => $linkedObject,
                    'title' => $fieldCfg['title'],
                    'titleField' => $linkedCfg->getLinkTitle()
                );
            }elseif($fieldObj->isObjectLink()){
                if($fieldObj->getLinkedObject() === 'medialib'){
                    $data[] = '{
									xtype:"medialibitemfield",
									resourceType:"all",
									name:"'.$field.'",
									readOnly:'.intval($readOnly).',
									fieldLabel:"'.$fieldCfg['title'].'"
								}';
                }else{

                    $data[] = '
						{
							xtype:"objectfield",
							objectName:"'.$fieldObj->getLinkedObject().'",
							controllerUrl:"'.$this->request->url([$this->appConfig->get('adminPath'), 'orm', 'dataview', '']).'",
							fieldLabel:"'.$fieldCfg['title'].'",
							name:"'.$field.'",
							anchor:"100%",
							readOnly:'.intval($readOnly).',
							isVc:'.intval($objectConfig->isRevControl()).'
						}
					';
                }
            }else{
                $newField = \Backend_Designer_Import::convertOrmFieldToExtField($field, $fieldCfg);

                if($newField !== false){
                    $newField->setName($field);

                    if($readOnly && $newField->getConfig()->isValidProperty('readOnly')){
                        $newField->readOnly = true;
                    }

                    if($fieldObj->isText() && $fieldObj->isHtml()){
                        $tabs[] = $newField->__toString();
                    }else{
                        $data[] = $newField->__toString();
                    }
                }
            }
        }

        $tab->items = '['.implode(',', $data).']';

        $this->response->success(array(
            'related' => $related,
            'fields' => str_replace(array("\n", "\t"), '', '['.implode(',', $tabs).']'),
            'readOnly' => intval($readOnly),
            'primaryKey' => $objectConfig->getPrimaryKey()
        ));
    }

    public function editorAction(){
        $router = new \Dvelum\App\Router\Backend();
        $router->runController(
            'Backend_Orm_Dataview_Editor', $this->request->getPart(4),
            $this->request, $this->response
        );
    }

    public function editorvcAction(){
        $router = new \Dvelum\App\Router\Backend();
        $router->runController(
            'Backend_Orm_Dataview_Editor_Vc', $this->request->getPart(4),
            $this->request, $this->response
        );
    }

    public function otitleAction(){
        $object = $this->request->post('object', 'string', false);
        $id = $this->request->post('id', 'string', false);

        if(!$object || !Service::get('orm')->configExists($object)){
            $this->response->error($this->lang->WRONG_REQUEST);
            return;
        }

        try{
            $o = Orm\Object::factory($object, $id);
            $this->response->success(array('title' => $o->getTitle()));
        }catch(\Exception $e){
            \Model::factory($object)->logError('Cannot get title for '.$object.':'.$id);
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }
    }
}
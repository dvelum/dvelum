<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2013  Kirill A Egorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * This is the base class for creating controllers of the
 * CRUD backend applications (creating, editing, updating, deleting)
 * for ORM objects
 */
abstract class Backend_Controller_Crud extends Backend_Controller
{
    /**
     * Current Db_Object name
     * @var string
     */
    protected $_objectName = false;
    /**
     * List of ORM objects accepted via linkedlistAction and otitleAction
     * @var array
     */
    protected $_canViewObjects = [];
    /**
     * List of ORM object field names displayed in the main list (listAction)
     * They may be assigned a value,
     * as well as an array
     */
    protected $_listFields = '*';
    /**
     * List of ORM object link fields displayed with related values in the main list (listAction)
     * (dictionary, object link, object list) key - result field, value - object field
     * object field will be used as result field for numeric keys
     * Requires primary key in result set
     * @var array
     */
    protected $_listLinks = [];

    public function __construct()
    {
        parent::__construct();
        $this->_objectName = $this->getObjectName();
        $this->_canViewObjects[] = $this->_objectName;
        $this->_canViewObjects = array_map('strtolower', $this->_canViewObjects);
    }

   /**
    * Get name of the object, which edits the controller
    * @return string
    */
    public function getObjectName()
    {
        return str_replace(array('Backend_', '_Controller') , '' , get_called_class());
    }

    /**
     * Get list of items. Returns JSON reply with
     * ORM object field data or return array with data and count;
     * Filtering, pagination and search are available
     * Sends JSON reply in the result
     * and closes the application (by default).
     * @throws Exception
     * @return void
     */
    public function listAction()
    {
        $result = $this->_getList();
        if(empty($result)){
            Response::jsonSuccess([]);
        }else{
            Response::jsonArray($result);
        }
    }

    /**
     * Prepare data for listAction
     * @return array
     * @throws Exception
     */
    protected function _getList()
    {
        $pager = Request::post('pager' , 'array' , []);
        $filter = Request::post('filter' , 'array' , []);
        $query = Request::post('search' , 'string' , false);
        $filter = array_merge($filter , Request::extFilters());

        $dataModel = Model::factory($this->_objectName);

        $data = $dataModel->getListVc($pager , $filter , $query , $this->_listFields);

        if(empty($data))
            return [];

        if(!empty($this->_listLinks)){
            $objectConfig = Db_Object_Config::getInstance($this->_objectName);
            if(!in_array($objectConfig->getPrimaryKey(),$this->_listFields,true)){
                throw new Exception('listLinks requires primary key for object '.$objectConfig->getName());
            }
            $this->addLinkedInfo($objectConfig, $this->_listLinks, $data, $objectConfig->getPrimaryKey());
        }
        return ['data' =>$data , 'count'=> $dataModel->getCount($filter , $query)];
    }

    /**
     * Get ORM object data
     * Sends a JSON reply in the result and
     * closes the application
     */
    public function loaddataAction()
    {
        $result = $this->_getData();
        if(empty($result))
            Response::jsonError($this->_lang->get('CANT_EXEC'));
        else
            Response::jsonSuccess($result);
    }

    /**
     * Prepare data for loaddataAction
     * @return array
     * @throws Exception
     */
    protected function _getData()
    {
        $id = Request::post('id' , 'integer' , false);

        if(!$id)
            return [];

        try{
            $obj = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
            Model::factory($this->_objectName)->logError($e->getMessage());
            return [];
        }

        $data = $obj->getData();

        /*
         * Prepare object list properties
         */
        $linkedObjects = $obj->getConfig()->getLinks([Db_Object_Config::LINK_OBJECT_LIST]);

        foreach($linkedObjects as $linkObject => $fieldCfg){
            foreach($fieldCfg as $field => $linkCfg){
                $data[$field] = $this->_collectLinksData($field , $obj , $linkObject);
            }
        }
        $data['id'] = $obj->getId();
        return $data;
    }

    /**
     * Get ready the data for fields of the ‘link to object list’ type;
     * Takes an array of identifiers as a parameter. expands the data adding object name,
     * status (deleted or not deleted), publication status for objects under
     * version control (used in child classes)
     * The provided data is necessary for the RelatedGridPanel component,
     * which is used for visual representation of relationship management.
     * @param string $fieldName
     * @param Db_Object $object
     * @param string $targetObjectName
     * @return array
     */
    protected function _collectLinksData($fieldName, Db_Object $object , $targetObjectName)
    {
        $result = [];

        $data = $object->get($fieldName);

        if(!empty($data))
        {
            $list = Db_Object::factory($targetObjectName , $data);
            $isVc = Db_Object_Config::getInstance($targetObjectName)->isRevControl();
            foreach($data as $id){
                if(isset($list[$id])){
                    $result[] = [
                        'id' => $id,
                        'deleted' => 0,
                        'title' => $list[$id]->getTitle(),
                        'published' => $isVc?$list[$id]->get('published'):1
                    ];

                }else{
                    $result[] = [
                        'id' => $id,
                        'deleted' => 1,
                        'title' => $id,
                        'published' => 0
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * Create/edit object data
     * The type of operation is defined as per the parameters being transferred
     * Sends JSON reply in the result and
     * closes the application
     */
    public function editAction()
    {
        $id = Request::post('id' , 'integer' , false);
        if(! $id)
            $this->createAction();
        else
            $this->updateAction();
    }

    /**
     * Create object
     * Sends JSON reply in the result and
     * closes the application
     */
    public function createAction()
    {
        $this->_checkCanEdit();
        $this->insertObject($this->getPostedData($this->_objectName));
    }

    /**
     * Update object data
     * Sends JSON reply in the result and
     * closes the application
     */
    public function updateAction()
    {
        $this->_checkCanEdit();
        $this->updateObject($this->getPostedData($this->_objectName));
    }

    /**
     * Delete object
     * Sends JSON reply in the result and
     * closes the application
     */
    public function deleteAction()
    {
        $this->_checkCanDelete();
        $id = Request::post('id' , 'integer' , false);

        if(!$id)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        try{
            $object = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }
        
        $acl = $object->getAcl();
        if($acl && !$acl->canDelete($object))
            Response::jsonError($this->_lang->CANT_DELETE);

        if($this->_configMain->get('vc_clear_on_delete'))
            Model::factory('Vc')->removeItemVc($this->_objectName , $id);

        if(!$object->delete())
            Response::jsonError($this->_lang->CANT_EXEC);

        Response::jsonSuccess();
    }

    /**
     * Save new ORM object (insert data)
     * Sends JSON reply in the result and
     * closes the application
     * @param Db_Object $object
     * @return void
     */
    public function insertObject(Db_Object $object)
    {
        if(!$recId = $object->save())
            Response::jsonError($this->_lang->CANT_CREATE);

        Response::jsonSuccess(array('id' => $recId));
    }

    /**
     * Update ORM object data
     * Sends JSON reply in the result and
     * closes the application
     * @param Db_Object $object
     */
    public function updateObject(Db_Object $object)
    {
        if(!$object->save())
            Response::jsonError($this->_lang->CANT_EXEC);

        Response::jsonSuccess(array('id' => $object->getId()));
    }

    /**
     * Get list of objects which can be linked
     */
    public function linkedlistAction()
    {
        $object = Request::post('object', 'string', false);
        $pager = Request::post('pager' , 'array' , array());
        $query = Request::post('search' , 'string' , false);

        if($object === false || !Db_Object_Config::configExists($object))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if(!in_array(strtolower($object), $this->_canViewObjects , true))
        	Response::jsonError($this->_lang->CANT_VIEW);

        $objectCfg = Db_Object_Config::getInstance($object);
        $primaryKey = $objectCfg->getPrimaryKey();

        $objectConfig = Db_Object_Config::getInstance($object);
        // Check ACL permissions
        $acl = $objectConfig->getAcl();
        if($acl){
            if(!$acl->can(Db_Object_Acl::ACCESS_VIEW , $object)){
                Response::jsonError($this->_lang->get('ACL_ACCESS_DENIED'));
            }
        }
        /**
         * @var Model
         */
        $model = Model::factory($object);
        $rc = $objectCfg->isRevControl();

        if($objectCfg->isRevControl())
            $fields = array('id'=>$primaryKey, 'published');
        else
            $fields = array('id'=>$primaryKey);

        $count = $model->getCount(false , $query ,false);
        $data = array();
        if($count)
        {
             $data = $model->getList($pager, false, $fields , false , $query);

            if(!empty($data))
            {
                $objectIds = Utils::fetchCol('id' , $data);
                try{
                    $objects = Db_Object::factory($object ,$objectIds);
                }catch (Exception $e){
                    Model::factory($object)->logError('linkedlistAction ->'.$e->getMessage());
                    Response::jsonError($this->_lang->get('CANT_EXEC'));
                }

                foreach ($data as &$item)
                {
                    if(!$rc)
                        $item['published'] = true;


                    $item['deleted'] = false;

                    if(isset($objects[$item['id']])){
                        $o = $objects[$item['id']];
                        $item['title'] = $o->getTitle();
                        if($rc)
                            $item['published'] = $o->get('published');
                    }else{
                        $item['title'] = $item['id'];
                    }

                }unset($item);
            }
        }
        Response::jsonSuccess($data,array('count'=>$count));
    }
    /**
     * Get object title
     */
    public function otitleAction()
    {
        $object = Request::post('object','string', false);
        $id = Request::post('id', 'string', false);

        if(!$object || !Db_Object_Config::configExists($object))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if(!in_array(strtolower($object), $this->_canViewObjects , true))
            Response::jsonError($this->_lang->CANT_VIEW);

        $objectConfig = Db_Object_Config::getInstance($object);
        // Check ACL permissions
        $acl = $objectConfig->getAcl();
        if($acl){
            if(!$acl->can(Db_Object_Acl::ACCESS_VIEW , $object)){
                Response::jsonError($this->_lang->get('ACL_ACCESS_DENIED'));
            }
        }

        try {
            $o = Db_Object::factory($object, $id);
            Response::jsonSuccess(array('title'=>$o->getTitle()));
        }catch (Exception $e){
            Model::factory($object)->logError('Cannot get title for '.$object.':'.$id);
            Response::jsonError($this->_lang->get('CANT_EXEC'));
        }
    }


    /**
     * Add related objects info into getList results
     * @param Db_Object_Config $cfg
     * @param array $fieldsToShow  list of link fields to process ( key - result field, value - object field)
     * object field will be used as result field for numeric keys
     * @param array & $data rows from  Model::getList result
     * @param string $pKey - name of Primary Key field in $data
     * @throws Exception
     */
    protected function addLinkedInfo(Db_Object_Config $cfg, array $fieldsToShow, array  & $data, $pKey)
    {
        $fieldsToKeys = [];
        foreach($fieldsToShow as $key=>$val){
            if(is_numeric($key)){
                $fieldsToKeys[$val] = $val;
            }else{
                $fieldsToKeys[$val] = $key;
            }
        }

        $links = $cfg->getLinks(
            [
                Db_Object_Config::LINK_OBJECT,
                Db_Object_Config::LINK_OBJECT_LIST,
                Db_Object_Config::LINK_DICTIONARY
            ],
            false
        );

        foreach($fieldsToShow as $resultField => $objectField)
        {
            if(!isset($links[$objectField]))
                throw new Exception($objectField.' is not Link');
        }

        foreach ($links as $field=>$config)
        {
            if(!isset($fieldsToKeys[$field])){
                unset($links[$field]);
            }
        }

        $rowIds = Utils::fetchCol($pKey , $data);
        $rowObjects = Db_Object::factory($cfg->getName() , $rowIds);
        $listedObjects = [];

        foreach($rowObjects as $object)
        {
            foreach ($links as $field=>$config)
            {
                if($config['link_type'] === Db_Object_Config::LINK_DICTIONARY){
                    continue;
                }

                if(!isset($listedObjects[$config['object']])){
                    $listedObjects[$config['object']] = [];
                }

                $oVal = $object->get($field);

                if(!empty($oVal))
                {
                    if(!is_array($oVal)){
                        $oVal = [$oVal];
                    }
                    $listedObjects[$config['object']] = array_merge($listedObjects[$config['object']], array_values($oVal));
                }
            }
        }

        foreach($listedObjects as $object => $ids){
            $listedObjects[$object] = Db_Object::factory($object, array_unique($ids));
        }

        foreach ($data as &$row)
        {
            if(!isset($rowObjects[$row[$pKey]]))
                continue;

            foreach ($links as $field => $config)
            {
                $list = [];
                $rowObject = $rowObjects[$row[$pKey]];
                $value = $rowObject->get($field);

                if(!empty($value))
                {
                    if($config['link_type'] === Db_Object_Config::LINK_DICTIONARY)
                    {
                        $dictionary = Dictionary::factory($config['object']);
                        if($dictionary->isValidKey($value)){
                            $row[$fieldsToKeys[$field]] = $dictionary->getValue($value);
                        }
                        continue;
                    }

                    if(!is_array($value))
                        $value = [$value];

                    foreach($value as $oId)
                    {
                        if(isset($listedObjects[$config['object']][$oId])){
                            $list[] = $this->linkedInfoObjectRenderer($rowObject, $field, $listedObjects[$config['object']][$oId]);
                        }else{
                            $list[] = '[' . $oId . '] ('.$this->_lang->get('DELETED').')';
                        }
                    }
                }
                $row[$fieldsToKeys[$field]] = implode(', ', $list);
            }
        }unset($row);
    }

    /**
     * String representation of related object for addLinkedInfo method
     * @param Db_Object $rowObject
     * @param string $field
     * @param Db_Object $relatedObject
     * @return string
     */
    protected function linkedInfoObjectRenderer(Db_Object $rowObject, $field, Db_Object $relatedObject)
    {
        return $relatedObject->getTitle();
    }
}
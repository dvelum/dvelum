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
    protected $_canViewObjects = array();
    /**
     * List of ORM object field names displayed in the main list (listAction)
     * They may be assigned a value,
     * as well as an array
     */
    protected $_listFields = '*';

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
     * Get list of items. Returns JSON  reply with
     * ORM object field data;
     * Filtering, pagination and search are available
     * Sends JSON reply in the result
     * and closes the application.
     */
    public function listAction()
    {
        $pager = Request::post('pager' , 'array' , array());
        $filter = Request::post('filter' , 'array' , array());
        $query = Request::post('search' , 'string' , false);

        $filter = array_merge($filter , Request::extFilters());

        $dataModel = Model::factory($this->_objectName);

        $data = $dataModel->getListVc($pager , $filter , $query ,
                $this->_listFields);

        if(empty($data))
            Response::jsonSuccess(array() , array('count' => 0 ));

        Response::jsonSuccess($data , array('count' => $dataModel->getCount($filter , $query)));
    }

    /**
     * Get ORM object data
     * Sends a JSON reply in the result and
     * closes the application
     */
    public function loaddataAction()
    {
        $id = Request::post('id' , 'integer' , false);

        if(! $id)
            Response::jsonSuccess(array());

        try{
            $obj = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->CANT_EXEC);
        }

        $data = $obj->getData();

        /*
         * Prepare object list properties
         */
        $linkedObjects = $obj->getConfig()->getLinks(array('multy'));

        foreach($linkedObjects as $linkObject => $fieldCfg){
            foreach($fieldCfg as $field => $linkCfg){
                $data[$field] = $this->_collectLinksData($field , $obj , $linkObject);
            }
        }
        $data['id'] = $obj->getId();
        /*
         * Send response
         */
        Response::jsonSuccess($data);
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
        if($object->getConfig()->isRevControl()){
           $result = $this->_collectVcLinks($fieldName , $object, $targetObjectName);
        }else{
           $result = $this->_collectLinks($fieldName , $object, $targetObjectName);
        }
        return $result;
    }

    /**
     * Collect data for "Object List" field under Data Version Control
     * @param $fieldName
     * @param Db_Object $object
     * @param $targetObjectName
     * @return array
     */
    protected function _collectVcLinks($fieldName , Db_Object $object , $targetObjectName)
    {
        $result = [];
        $data = $object->getData();
        $data = $data[$fieldName];

        if(empty($data))
            return array();

        $ids = Utils::fetchCol('id' , $data);
        $data = Utils::rekey('id' , $data);

        $objectConfig = Db_Object_Config::getInstance($targetObjectName);
        $model = Model::factory(ucfirst($targetObjectName));

        try{
            $objectsList = Db_Object::factory(ucfirst($targetObjectName) , $ids);
        }catch (Exception $e){
            $objectsList =  array();
        }
        /*
         * Find out deleted records
         */
        if(empty($objectsList)){
            $deleted = $ids;
        }else{
            $deleted = array_diff($ids , array_keys($objectsList));
        }

        $result = array();
        foreach($ids as $id)
        {
            if(in_array($id , $deleted)){
                $item = array(
                    'id' => $id,
                    'deleted' => 1,
                    'title' => $data[$id]['title'],
                    'published' => 0
                );
            }else{
                $dataObject =  $objectsList[$id];
                $item = array(
                    'id' => $id,
                    'deleted' => 0,
                    'title' => $dataObject->getTitle(),
                    'published' => $dataObject->get('published')
                );
            }
            $result[] = $item;
        }
        return $result;
    }

    /**
     * Collect data for "Object List" field
     * @param $fieldName
     * @param Db_Object $object
     * @param $targetObjectName
     * @return array
     */
    protected function _collectLinks($fieldName , Db_Object $object , $targetObjectName)
    {
        $result = [];
        $srcObjectConfig = $object->getConfig();

        if($srcObjectConfig->isManyToManyLink($fieldName))
        {
            $linksObject = $srcObjectConfig->getRelationsObject($fieldName);
            $model = Model::factory($linksObject);
            $data = $model->getList(
                ['sort'=>'order_no','dir'=>'ASC'],
                ['source_id' => $object->getId()],
                [
                    'id' => 'target_id'
                ]
            );
        }else{
            $linksObject = $this->_configMain->get('orm_links_object');
            $model = Model::factory($linksObject);
            $data = $model->getList(
                ['sort'=>'order','dir'=>'ASC'],
                [
                    'src' => $object->getName(),
                    'src_id' => $object->getId(),
                    'src_field' =>$fieldName,
                    'target' => $targetObjectName
                ],
                [
                    'id' => 'target_id'
                ]
            );
        }

        if(!empty($data))
        {
            $list = Db_Object::factory($targetObjectName , Utils::fetchCol('id',$data));
            $isVc = Db_Object_Config::getInstance($targetObjectName)->isRevControl();
            foreach($data as $value){
                if(isset($list[$value['id']])){
                    $result[] = [
                        'id' => $value['id'],
                        'deleted' => 0,
                        'title' => $list[$value['id']]->getTitle(),
                        'published' => $isVc?$list[$value['id']]->get('published'):1
                    ];

                }else{
                    $result[] = [
                        'id' => $value['id'],
                        'deleted' => 1,
                        'title' => $value['id'],
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

                    if(isset($objects[$item[$primaryKey]])){
                        $o = $objects[$item[$primaryKey]];
                        $item['title'] = $o->getTitle();
                        if($rc)
                            $item['published'] = $data['published'];
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
}
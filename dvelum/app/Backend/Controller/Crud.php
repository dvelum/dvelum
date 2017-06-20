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
use Dvelum\Config;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\App\Data;
use Dvelum\App\Controller\EventManager;
use Dvelum\Orm\ObjectInterface;

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

    /**
     * @var Data\Api\Request
     */
    protected $apiRequest;

    public function __construct()
    {
        parent::__construct();
        $this->_objectName = $this->getObjectName();
        $this->_canViewObjects[] = $this->_objectName;
        $this->_canViewObjects = array_map('strtolower', $this->_canViewObjects);

        $this->apiRequest = $this->getApiRequest($this->request);
        $this->initListeners();
    }

    /**
     *  Event listeners can be defined here
     */
    public function initListeners(){}


    /**
     * @return Data\Api
     */
    protected function getApi(Data\Api\Request $request, \User $user) : Data\Api
    {
        return new Data\Api($request, $user);
    }

    /**
     * @param Dvelum\Request $request
     * @return Data\Api\Request
     */
    protected function  getApiRequest(Dvelum\Request $request) : Data\Api\Request
    {
        return new Data\Api\Request($request);
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
        if(!$this->eventManager->fireEvent(EventManager::BEFORE_LIST, new stdClass())){
            Response::jsonError($this->eventManager->getError());
        }

        $result = $this->_getList();
        if(empty($result)){
            $result = ['data'=>[],'count'=>0];
        }

        $eventData = new stdClass();
        $eventData->data = $result['data'];
        $eventData->count = $result['count'];

        if(!$this->eventManager->fireEvent(EventManager::AFTER_LIST, $eventData)){
            Response::jsonError($this->eventManager->getError());
        }

        Response::jsonSuccess(
            $eventData->data,
            ['count'=>$eventData->count]
        );
    }

    /**
     * Prepare data for listAction
     * backward compatibility
     * @return array
     * @throws Exception
     */
    protected function _getList()
    {
        $api = $this->getApi($this->apiRequest, $this->_user);
        $count = $api->getCount();

        if(!$count){
            return ['data'=>[],'count'=>0];
        }

        $data = $api->getList();

        if(!empty($this->_listLinks)){
            $objectConfig = Orm\Object\Config::factory($this->_objectName);
            if(is_array($this->_listFields) && !in_array($objectConfig->getPrimaryKey(),$this->_listFields,true)){
                throw new Exception('listLinks requires primary key for object '.$objectConfig->getName());
            }
            $this->addLinkedInfo($objectConfig, $this->_listLinks, $data, $objectConfig->getPrimaryKey());
        }

        return ['data' =>$data , 'count'=> $count];
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
            $obj = Db_Object::factory($this->_objectName , $id);
        }catch(Exception $e){
            Model::factory($this->_objectName)->logError($e->getMessage());
            return [];
        }

        $data = $obj->getData();

        /*
         * Prepare object list properties
         */
        $linkedObjects = $obj->getConfig()->getLinks([Orm\Object\Config::LINK_OBJECT_LIST]);

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
     * @param ObjectInterface $object
     * @param string $targetObjectName
     * @return array
     */
    protected function _collectLinksData($fieldName, ObjectInterface $object , $targetObjectName)
    {
        $result = [];

        $data = $object->get($fieldName);

        if(!empty($data))
        {
            $list = Orm\Object::factory($targetObjectName , $data);
            $isVc = Orm\Object\Config::factory($targetObjectName)->isRevControl();
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
            $object =  Orm\Object::factory($this->_objectName , $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }

        $acl = $object->getAcl();
        if($acl && !$acl->canDelete($object))
            Response::jsonError($this->_lang->CANT_DELETE);

        $ormConfig = Config::storage()->get('orm');

        if($ormConfig>get('vc_clear_on_delete'))
            Model::factory('Vc')->removeItemVc($this->_objectName , $id);

        if(!$object->delete())
            Response::jsonError($this->_lang->CANT_EXEC);

        Response::jsonSuccess();
    }

    /**
     * Save new ORM object (insert data)
     * Sends JSON reply in the result and
     * closes the application
     * @param Orm\Object $object
     * @return void
     */
    public function insertObject(ObjectInterface  $object)
    {
        if(!$recId = $object->save())
            Response::jsonError($this->_lang->CANT_CREATE);

        Response::jsonSuccess(array('id' => $recId));
    }

    /**
     * Update ORM object data
     * Sends JSON reply in the result and
     * closes the application
     * @param \Db_Object  $object
     */
    public function updateObject(ObjectInterface  $object)
    {
        if(!$object->save())
            Response::jsonError($this->_lang->CANT_EXEC);

        Response::jsonSuccess(array('id' => $object->getId()));
    }

    /**
     * Get list of objects which can be linked
     */
    protected function getRelatedObjectsInfo()
    {
        $object = Request::post('object', 'string', false);
        $filter = Request::post('filter' , 'array' , []);
        $pager = Request::post('pager' , 'array' , []);
        $query = Request::post('search' , 'string' , null);
        $filter = array_merge($filter , Request::extFilters());

        if($object === false || !Orm\Object\Config::configExists($object))
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        if(!in_array(strtolower($object), $this->_canViewObjects , true))
            Response::jsonError($this->_lang->get('CANT_VIEW'));

        $objectCfg = Orm\Object\Config::factory($object);
        $primaryKey = $objectCfg->getPrimaryKey();

        $objectConfig = Orm\Object\Config::factory($object);

        // Check ACL permissions
        $acl = $objectConfig->getAcl();
        if($acl){
            if(!$acl->can(Orm\Object\Acl::ACCESS_VIEW , $object)){
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

        $dataQuery = $model->query()->filters($filter)->params($pager)->search($query)->fields($fields);
        $count = $dataQuery->getCount();
        $data = array();
        if($count)
        {
            $data = $dataQuery->fetchAll();

            if(!empty($data))
            {
                $objectIds = Utils::fetchCol('id' , $data);
                try{
                    $objects = Orm\Object::factory($object ,$objectIds);
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
        return ['data'=>$data,'count'=>$count];
    }

    /**
     * Get list of objects which can be linked
     */
    public function linkedListAction()
    {
        $result = $this->getRelatedObjectsInfo();

        if(empty($result)){
            Response::jsonSuccess([]);
        }else{
            Response::jsonArray($result);
        }
    }


    /**
     * Get object title
     */
    public function objectTitleAction()
    {
        $object = Request::post('object','string', false);
        $id = Request::post('id', 'string', false);

        if(!$object || !Orm\Object\Config::configExists($object))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if(!in_array(strtolower($object), $this->_canViewObjects , true))
            Response::jsonError($this->_lang->CANT_VIEW);

        $objectConfig = Orm\Object\Config::factory($object);
        // Check ACL permissions
        $acl = $objectConfig->getAcl();
        if($acl){
            if(!$acl->can(Orm\Object\Acl::ACCESS_VIEW , $object)){
                Response::jsonError($this->_lang->get('ACL_ACCESS_DENIED'));
            }
        }

        try {
            $o = Orm\Object::factory($object, $id);
            Response::jsonSuccess(array('title'=>$o->getTitle()));
        }catch (Exception $e){
            Model::factory($object)->logError('Cannot get title for '.$object.':'.$id);
            Response::jsonError($this->_lang->get('CANT_EXEC'));
        }
    }

    /**
     * Get object title
     * @deprecated
     */
    public function otitleAction()
    {
        $this->objectTitleAction();
    }
}
<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net Copyright
 * (C) 2011-2013 Kirill A Egorov This program is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version. This program is distributed
 * in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details. You should have received
 * a copy of the GNU General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 */

/**
 * This is the base class for implementing CRUD backend interfaces
 * (creating, editing, updating and deleting)
 * for ORM object under version control
 */
abstract class Backend_Controller_Crud_Vc extends Backend_Controller_Crud
{
    /**
     * (non-PHPdoc)
     * @see Backend_Controller::indexAction()
     */
    public function indexAction()
    {
        parent::indexAction();

        $this->_resource->addInlineJs('
            var canPublish =  ' . intval($this->_user->canPublish($this->_module)) . ';
        ');
        $this->_resource->addJs('/js/app/system/ContentWindow.js' , 1);
        $this->_resource->addJs('/js/app/system/RevisionPanel.js' , 2);
    }

    /**
     * Check object owner
     * @param Db_Object $object
     */
    protected function _checkOwner(Db_Object $object)
    {
        if(!$object->getConfig()->isRevControl()){
            return;
        }

        if($this->_user->onlyOwnRecords($this->getModule()) && $object->author_id !== $this->_user->getId()){
            Response::jsonError($this->_lang->CANT_ACCESS);
        }
    }
    /**
     * Check edit permissions
     */
    protected function _checkCanEdit()
    {
        parent::_checkCanEdit();
    }
    /**
     * Check delete permissions
     */
    protected function _checkCanDelete()
    {
        parent::_checkCanDelete();

    }
    /**
     * Check for permissions to publish the object
     */
    protected function _checkCanPublish()
    {
        if(!User::getInstance()->canPublish($this->_module))
            Response::jsonError($this->_lang->CANT_PUBLISH);
    }

    /**
     * (non-PHPdoc)
     * @see Backend_Controller_Crud::_getList()
     */
    protected function _getList()
    {
        $pager = Request::post('pager' , 'array' , array());
        $filter = Request::post('filter' , 'array' , array());
        $query = Request::post('search' , 'string' , false);
        $filter = array_merge($filter , Request::extFilters());

        if($this->_user->onlyOwnRecords($this->_module)){
            $filter['author_id'] = $this->_user->getId();
        }

        $dataModel = Model::factory($this->_objectName);
        $vc = Model::factory('vc');

        $data = $dataModel->getListVc($pager , $filter , $query , $this->_listFields , 'user' , 'updater');

        if(empty($data))
            return [];

        $ids = Utils::fetchCol('id' , $data);

        if(!empty($this->_listLinks)){
            $objectConfig = Db_Object_Config::getInstance($this->_objectName);
            if(!in_array($objectConfig->getPrimaryKey(),$this->_listFields,true)){
                throw new Exception('listLinks requires primary key for object '.$objectConfig->getName());
            }
            $this->addLinkedInfo($objectConfig, $this->_listLinks, $data, $objectConfig->getPrimaryKey());
        }
        return ['data'=> $data, 'count'=> $dataModel->getCount($filter , $query)];
    }

    /**
     * Get the object data ready to be sent
     * @param Db_Object $object
     * @param integer $version
     * @return array
     */
    protected function _loadData(Db_Object $object , $version)
    {
        $id = $object->getId();
        $vc = Model::factory('Vc');

        if(!$version)
            $version = $vc->getLastVersion($this->_objectName , $id);

        if($version)
        {
            try {
                $object->loadVersion($version);
            } catch (Exception $e) {
                Model::factory($object->getName())->logError('Cannot load version ' . $version . ' for ' . $object->getName() . ':' . $object->getId());
                Response::jsonError($this->_lang->get('CANT_LOAD'));
            }

            $data = $object->getData();

            if (empty($data)) {
                Response::jsonError($this->_lang->get('CANT_LOAD'));
            }

            $data['id'] = $id;
            $data['version'] = $version;
            $data['published'] = $object->get('published');
            $data['staging_url'] = static::getStagingUrl($object);

        }else{
            $data = $object->getData();
            $data['id'] = $object->getId();
        }

        /*
         * Prepare Object List properties
         */
        $linkedObjects = $object->getConfig()->getLinks([Db_Object_Config::LINK_OBJECT_LIST]);
        foreach($linkedObjects as $linkObject => $fieldCfg){
            foreach($fieldCfg as $field => $linkCfg){
                $data[$field] = $this->_collectLinksData($field, $object , $linkObject);
            }
        }
        return $data;
    }

    /**
     * Prepare data for loaddataAction
     * @return array
     * @throws Exception
     */
    protected function _getData()
    {
        $id = Request::post('id' , 'integer' , false);
        $version = Request::post('version' , 'integer' , 0);
        $data = array();

        if($id){
            try{
                $obj = new Db_Object($this->_objectName , $id);
            }catch(Exception $e){
                Model::factory($this->_objectName)->logError($e->getMessage());
                return [];
            }
            $this->_checkOwner($obj);
            $data = $this->_loadData($obj , $version);
        }
        return $data;
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
     * (non-PHPdoc)
     * @see Backend_Controller_Crud::deleteAction()
     */
    public function deleteAction()
    {
        $this->_checkCanDelete();
        $id = Request::post('id' , 'integer' , false);

        try{
            $object = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }

        $this->_checkOwner($object);

        if($this->_configMain->get('vc_clear_on_delete'))
            Model::factory('Vc')->removeItemVc($this->_objectName , $id);

        if(!$object->delete())
            Response::jsonError($this->_lang->CANT_EXEC);

        Response::jsonSuccess();
    }

    /**
     * Unpublish object
     * Sends JSON reply in the result
     * and closes the application.
     */
    public function unpublishAction()
    {
        $id = Request::post('id' , 'integer' , false);

        if(!$id)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $this->_checkCanPublish();

        try{
            $object = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->CANT_EXEC);
        }

        $this->_checkOwner($object);
        
        $acl = $object->getAcl();
        if($acl && !$acl->canPublish($object))
            Response::jsonError($this->_lang->CANT_PUBLISH);

        $this->unpublishObject($object);
    }

    /**
     * Publish object data changes
     * Sends JSON reply in the result
     * and closes the application.
     */
    public function publishAction()
    {
        $id = Request::post('id' , 'integer' , false);
        $vers = Request::post('vers' , 'integer' , false);

        if(!$id || !$vers)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $this->_checkCanPublish();

        try{
            $object = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->CANT_EXEC . '. ' .  $e->getMessage());
        }

        $this->_checkOwner($object);
        
        $acl = $object->getAcl();

        if($acl && !$acl->canPublish($object))
           Response::jsonError($this->_lang->CANT_PUBLISH);

        try{
			$object->loadVersion($vers);
        }catch(Exception $e){
            Response::jsonError($this->_lang->VERSION_INCOPATIBLE);
        }

        if(!$object->publish())
            Response::jsonError($this->_lang->CANT_EXEC);

        Response::jsonSuccess();
    }

    /**
     * Define the object data preview page URL
     * (needs to be redefined in the child class
     * as per the application structure)
     * @param Db_Object $object
     * @return string
     */
    public function getStagingUrl(Db_Object $object)
    {
        $routerClass =  $this->_configMain->get('frontend_router');
        $frontendRouter = new $routerClass();

        $stagingUrl = $frontendRouter->findUrl(strtolower($object->getName()));

        if(! strlen($stagingUrl))
            return Request::url(array('/'));

        return Request::url(array($stagingUrl,'item',$object->getId()));
    }

    /**
     * (non-PHPdoc)
     * @see Backend_Controller_Crud::insertObject()
     */
    public function insertObject(Db_Object $object)
    {
        $object->published = false;
        $object->author_id = User::getInstance()->id;
        $object->date_created = date('Y-m-d H:i:s');

        if(!$recId = $object->save())
            Response::jsonError($this->_lang->CANT_CREATE);

        $versNum = Model::factory('Vc')->newVersion($object);

        if(!$versNum)
            Response::jsonError($this->_lang->CANT_CREATE);

        $stagingUrl = $this->getStagingUrl($object);

        Response::jsonSuccess(
                array(
                        'id' => $recId,
                        'version' => $versNum,
                        'published' => false,
                        'staging_url' => $stagingUrl
                )
        );
    }

    /**
     * (non-PHPdoc)
     * @see Backend_Controller_Crud::updateObject()
     */
    public function updateObject(Db_Object $object)
    {
        $author = $object->get('author_id');
        if(empty($author)){
            $object->set('author_id' , $this->_user->getId());
        }else{
            $this->_checkOwner($object);
        }

        if(!$object->saveVersion())
        	Response::jsonError($this->_lang->CANT_CREATE);

        $stagingUrl = $this->getStagingUrl($object);

        Response::jsonSuccess(
                array(
                        'id' => $object->getId(),
                        'version' => $object->getVersion(),
                        'staging_url' => $stagingUrl,
                        'published_version' => $object->get('published_version'),
                        'published' => $object->get('published')
                )
        );
    }

    /**
     * Unpublish object
     * Sends JSON reply in the result
     * and closes the application.
     * @param Db_Object $object
     */
    public function unpublishObject(Db_Object $object)
    {
        if(!$object->get('published'))
            Response::jsonError($this->_lang->NOT_PUBLISHED);

        if(!$object->unpublish())
            Response::jsonError($this->_lang->CANT_EXEC);

        Response::jsonSuccess();
    }
}
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
     * Check for permissions to publish the object
     */
    protected function _checkCanPublish()
    {
        if(!User::getInstance()->canPublish($this->_module))
            Response::jsonError($this->_lang->CANT_PUBLISH);
    }

   /**
    * (non-PHPdoc)
    * @see Backend_Controller_Crud::listAction()
    */
    public function listAction()
    {
        $pager = Request::post('pager' , 'array' , array());
        $filter = Request::post('filter' , 'array' , array());
        $query = Request::post('search' , 'string' , false);
        
        $filter = array_merge($filter , Request::extFilters());
        
        $dataModel = Model::factory($this->_objectName);
        $vc = Model::factory('vc');

        $data = $dataModel->getListVc($pager , $filter , $query ,
                $this->_listFields , 'user' , 'updater');

        if(empty($data))
            Response::jsonSuccess(array() , array( 'count' => 0));

        $ids = Utils::fetchCol('id' , $data);

        $maxRevisions = $vc->getLastVersion($this->_objectName , $ids);

        foreach($data as $k => &$v)
            if(isset($maxRevisions[$v['id']]))
                $v['last_version'] = $maxRevisions[$v['id']];
            else
                $v['last_version'] = 0;

        Response::jsonSuccess($data , array('count' => $dataModel->getCount($filter , $query)));
    }

    /**
     * Get the object data ready to be sent
     * @param Db_Object $object
     * @param integer $id
     * @param integer $version
     * @return array
     */
    protected function _loadData(Db_Object $object , $version)
    {
        $id = $object->getId();
        $vc = Model::factory('Vc');

        try{
            $obj = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->CANT_EXEC);
        }

        if(!$version)
            $version = $vc->getLastVersion($this->_objectName , $id);

        if(!$version){
            $data = $obj->getData();
            $data['id'] = $obj->getId();
            Response::jsonSuccess($data);
        }

        $data = $vc->getData($this->_objectName , $id , $version);

        if(empty($data))
            Response::jsonError($this->_lang->CANT_LOAD);

        $data['id'] = $id;
        $data['version'] = $version;
        $data['published'] = $obj->get('published');
        $data['staging_url'] = static::getStagingUrl($obj);
        /*
         * Prepare multilink properties
         */
        $linkedObjects = $obj->getConfig()->getLinks(array('multy'));
        foreach($linkedObjects as $linkObject => $fieldCfg){
            foreach($fieldCfg as $field => $linkCfg){
                if(empty($data[$field]))
                    continue;

                $data[$field] = array_values(
                        $this->_collectLinksData($data[$field] , $linkObject));
            }
        }
        return $data;
    }

   /**
    * (non-PHPdoc)
    * @see Backend_Controller_Crud::loaddataAction()
    */
    public function loaddataAction()
    {
        $id = Request::post('id' , 'integer' , false);
        $version = Request::post('version' , 'integer' , 0);
        $data = array();

        if($id){
            try{
                $obj = new Db_Object($this->_objectName , $id);
            }catch(Exception $e){
                Response::jsonError($this->_lang->CANT_EXEC);
            }

            $data = $this->_loadData($obj , $version);
        }
        /*
         * Send response
         */
        Response::jsonSuccess($data);
    }

    /**
     * (non-PHPdoc)
     * @see Backend_Controller_Crud::deleteAction()
     */
    public function deleteAction()
    {
        $id = Request::post('id' , 'integer' , false);

        try{
            $object = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }

        if(!$id)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if(!User::getInstance()->canDelete($this->_objectName))
            Response::jsonError($this->_lang->CANT_DELETE);

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
        $frontendRouter = new Frontend_Router();

        $stagingUrl = $frontendRouter->findUrl(strtolower($object->getName()));

        if(! strlen($stagingUrl))
            return Request::url(array('404'));

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
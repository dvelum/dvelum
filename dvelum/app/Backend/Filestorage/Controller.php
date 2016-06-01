<?php
/**
 * Filestorage UI controller
 */
class Backend_Filestorage_Controller extends Backend_Controller_Crud
{
	protected $_listFields = array("path","date","ext","size","user_id","name","id");
    protected $_canViewObjects = array('user');

    public function indexAction()
    {
        $certStorage = Model::factory('Filestorage')->getStorage();
        $this->_resource->addInlineJs('app.filestorageConfig = '.json_encode($certStorage->getConfig()->get('uploader_config')).';');
        parent::indexAction();
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

        if(isset($filter['date']) && !empty($filter['date'])) {
            $date = date('Y-m-d', strtotime($filter['date']));
            $filter['date'] = new Db_Select_Filter('date' , array($date.' 00:00:00', $date.' 23:59:59') , Db_Select_Filter::BETWEEN);
        }

        $data = $dataModel->getListVc($pager , $filter , $query , $this->_listFields);

        if(empty($data))
            Response::jsonSuccess(array() , array('count' => 0 ));

        $userIds = Utils::fetchCol('user_id' , $data);
        $userData = array();
        if(!empty($userIds)){
            $userData = Model::factory('User')->getList(false , array('id'=>$userIds) , array('id','name'));
            if(!empty($userData)){
                $userData = Utils::rekey('id' , $userData);
        }

        foreach($data as $k=>&$v)
        {
            if(isset($userData[$v['user_id']])){
                $v['user_name'] = $userData[$v['user_id']]['name'];
            }else{
                $v['user_name'] = '';
            }
        }
        }unset($v);

        Response::jsonSuccess($data , array('count' => $dataModel->getCount($filter , $query)));
    }

    /**
     * Download file from filestorage
     */
    public function downloadAction()
    {
        $fileId = intval(Request::getInstance()->getPart(3));

        if(!$fileId){
            Response::redirect('/');
        }

        try{
            $file = new Db_Object('Filestorage' , $fileId);
        }catch (Exception $e){
            Response::redirect('/');
        }

        $storage = Model::factory('Filestorage')->getStorage();
        $storageConfig = $storage->getConfig()->__toArray();

        $storagePath = $storageConfig['filepath']. $file->get('path');

        if (!file_exists($storagePath)) {
            echo $this->_lang->get('FILE_NOT_FOUND');
            exit();
        }

        header('Content-Description: File Transfer');
       // header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . str_replace(' ' ,'_' , $file->get('name')));

        switch($storageConfig['download']['type']){
            case 'native' :
                readfile($storagePath);
                break;
            case 'apache' :
                $filePath = $storageConfig['redirect_path']. $file->get('path');
                header('X-Sendfile: ' . $filePath);
                break;
            case 'nginx' :
                $filePath = $storageConfig['redirect_path']. $file->get('path');
                header('X-Accel-Redirect: ' . $filePath);
                break;
        }
        exit();
    }

    /**
     * file upload
     */
    public function uploadAction()
    {
        $this->_checkCanEdit();

       $files = Request::files();

        if (!isset($files['file']) || empty($files['file']))
            Response::jsonError($this->_lang->get('FILL_FORM'));

        /**
         * @var Filestorage_Abstract $fileStorage
         */
        $fileStorage = Model::factory('Filestorage')->getStorage();

        $files = $fileStorage->upload();

        if (empty($files)) {
            Response::jsonError($this->_lang->get('CANT_EXEC'));
        }
        Response::jsonSuccess();
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
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        try{
            $object = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        $acl = $object->getAcl();
        if($acl && !$acl->canDelete($object))
            Response::jsonError($this->_lang->get('CANT_DELETE'));

        $fileStorage = Model::factory('Filestorage')->getStorage();

        if(!$fileStorage->remove($id)){
            Response::jsonError($this->_lang->get('CANT_EXEC'));
        }

        Response::jsonSuccess();
    }
} 
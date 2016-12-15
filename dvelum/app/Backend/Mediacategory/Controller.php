<?php

/**
 * Mediacategory module controller
 * Backoffice UI
 */
use Dvelum\Orm;
use Dvelum\Model;
use Dvelum\Config;

class Backend_Mediacategory_Controller extends Dvelum\App\Backend\Api\Controller
{
    public function getModule()
    {
        return 'Medialib';
    }

    /**
     * Get Categories tree
     */
    public function treeListAction()
    {
        $model = Model::factory('Mediacategory');;
        $this->response->json($model->getCategoriesTree());
    }

    /**
     * Sort media categories
     */
    public function sortCatalogAction()
    {
        $this->checkCanEdit();

        $id = $this->request->post('id','integer',false);
        $newParent = $this->request->post('newparent','integer',false);
        $order = $this->request->post('order', 'array' , array());

        if(!$id || !strlen($newParent) || empty($order))
           $this->response->error($this->lang->get('WRONG_REQUEST'));

        try{
            $pObject = Orm\Object::factory('mediacategory' , $id);
            $pObject->set('parent_id', $newParent);
            $pObject->save();
            Model::factory('Mediacategory')->updateSortOrder($order);
           $this->response->success();
        } catch (Exception $e){
           $this->response->error($this->lang->get('CANT_EXEC') . ' ' . $e->getMessage());
        }
    }

    /**
     * Delete object
     * Sends JSON reply in the result and
     * closes the application
     */
    public function deleteAction()
    {
        $this->_checkCanDelete();
        $id = $this->request->post('id' , 'integer' , false);

        if(!$id)
           $this->response->error($this->lang->get('WRONG_REQUEST'));

        try{
            $object = Orm\Object::factory($this->objectName , $id);
        }catch(Exception $e){
           $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $childCount = Model::factory('Mediacategory')->getCount(array('parent_id'=>$id));
        if($childCount)
           $this->response->error($this->lang->get('REMOVE_CHILDREN'));

        if($this->configMain->get('vc_clear_on_delete'))
            Model::factory('Vc')->removeItemVc($this->objectName , $id);

        $medialib = Model::factory('Medialib');

        $medialib->categoryRemoved($id);

        if(!$object->delete())
           $this->response->error($this->lang->get('CANT_EXEC'));

       $this->response->success();
    }

    /**
     * Change Medialibrary items category
     */
    public function placeItemsAction()
    {
        $this->_checkCanEdit();
        $items = $this->request->post('items', 'string', false);
        $category = $this->request->post('catalog', 'integer', false);

        if($items === false|| $category === false)
           $this->response->error($this->lang->get('WRONG_REQUEST'));

        $items = json_decode($items , true);
        
        if(!is_array($items) || empty($items))
           $this->response->error($this->lang->get('WRONG_REQUEST'));

        $medialibModel = Model::factory('Medialib');

        if($medialibModel->updateItemsCategory($items , $category))
           $this->response->success();
        else
           $this->response->error($this->lang->get('CANT_EXEC'));
    }
}
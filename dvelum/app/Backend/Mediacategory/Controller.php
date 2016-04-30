<?php

/**
 * Mediacategory module controller
 * Backoffice UI
 */
class Backend_Mediacategory_Controller extends Backend_Controller_Crud
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
        Response::jsonArray($model->getCategoriesTree());
    }

    /**
     * Sort media categories
     */
    public function sortCatalogAction()
    {
        $this->_checkCanEdit();

        $id = Request::post('id','integer',false);
        $newParent = Request::post('newparent','integer',false);
        $order = Request::post('order', 'array' , array());

        if(!$id || !strlen($newParent) || empty($order))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        try{
            $pObject = new Db_Object('mediacategory' , $id);
            $pObject->set('parent_id', $newParent);
            $pObject->save();
            Model::factory('Mediacategory')->updateSortOrder($order);
            Response::jsonSuccess();
        } catch (Exception $e){
            Response::jsonError($this->_lang->CANT_EXEC . ' ' . $e->getMessage());
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
        $id = Request::post('id' , 'integer' , false);

        if(!$id)
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        try{
            $object = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        $childCount = Model::factory('Mediacategory')->getCount(array('parent_id'=>$id));
        if($childCount)
            Response::jsonError($this->_lang->get('REMOVE_CHILDREN'));

        if($this->_configMain->get('vc_clear_on_delete'))
            Model::factory('Vc')->removeItemVc($this->_objectName , $id);

        $medialib = Model::factory('Medialib');

        $medialib->categoryRemoved($id);

        if(!$object->delete())
            Response::jsonError($this->_lang->get('CANT_EXEC'));

        Response::jsonSuccess();
    }

    /**
     * Change Medialibrary items category
     */
    public function placeItemsAction()
    {
        $this->_checkCanEdit();
        $items = Request::post('items', 'string', false);
        $category = Request::post('catalog', 'integer', false);

        if($items === false|| $category === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $items = json_decode($items , true);
        if(!is_array($items) || empty($items))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $medialibModel = Model::factory('Medialib');

        if($medialibModel->updateItemsCategory($items , $category))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_EXEC);
    }
}
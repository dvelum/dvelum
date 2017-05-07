<?php

namespace Dvelum\App\Backend\Api;

use Dvelum\App;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\App\Data;
use Dvelum\App\Session;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Config;
use Dvelum\App\Controller\EventManager;

class Controller extends App\Backend\Controller
{
    /**
     * List of ORM objects accepted via linkedListAction and otitleAction
     * @var array
     */
    protected $canViewObjects = [];
    /**
     * List of ORM object link fields displayed with related values in the main list (listAction)
     * (dictionary, object link, object list) key - result field, value - object field
     * object field will be used as result field for numeric keys
     * Requires primary key in result set
     * @var array
     */
    protected $listLinks = [];
    /**
     * Controller events manager
     * @var App\Controller\EventManager
     */
    protected $eventManager;

    /**
     * API Request object
     * @var Data\Api\Request
     */
    protected $apiRequest;

    /**
     * Controller constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $this->apiRequest = $this->getApiRequest($this->request);
        $this->eventManager = new App\Controller\EventManager();
        $this->canViewObjects[] = $this->objectName;
        $this->canViewObjects = \array_map('strtolower', $this->canViewObjects);

        $this->initListeners();
    }


    /**
     *  Event listeners can be defined here
     */
    public function initListeners(){}

    /**
     * @param Data\Api\Request $request
     * @param Session\User $user
     * @return Data\Api
     */
    protected function getApi(Data\Api\Request $request, Session\User $user) : Data\Api
    {
        return new Data\Api($request, $user);
    }

    /**
     * @param Request $request
     * @return Data\Api\Request
     */
    protected function getApiRequest(Request $request) : Data\Api\Request
    {
        $request = new Data\Api\Request($request);
        $request->setObject($this->getObjectName());
        return $request;
    }

    /**
     * Get list of objects which can be linked
     */
    public function linkedListAction()
    {
        $object = $this->request->post('object', 'string', false);
        $filter = $this->request->post('filter' , 'array' , []);
        $pager = $this->request->post('pager' , 'array' , []);
        $query = $this->request->post('search' , 'string' , false);
        $filter = array_merge($filter , $this->request->extFilters());

        if($object === false || !Orm\Object\Config::configExists($object))
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        if(!in_array(strtolower($object), $this->canViewObjects , true))
            $this->response->error($this->lang->get('CANT_VIEW'));

        $objectCfg = Orm\Object\Config::factory($object);
        $primaryKey = $objectCfg->getPrimaryKey();

        $objectConfig = Orm\Object\Config::factory($object);

        // Check ACL permissions
        $acl = $objectConfig->getAcl();

        if($acl){
            if(!$acl->can(Orm\Object\Acl::ACCESS_VIEW , $object)){
                $this->response->error($this->lang->get('ACL_ACCESS_DENIED'));
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
            $data = $model->getList($pager, $filter, $fields , false , $query);

            if(!empty($data))
            {
                $objectIds = \Utils::fetchCol('id' , $data);
                try{
                    $objects = Orm\Object::factory($object ,$objectIds);
                }catch (\Exception $e){
                    Model::factory($object)->logError('linkedlistAction ->'.$e->getMessage());
                    $this->response->error($this->lang->get('CANT_EXEC'));
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
        $this->response->success($data, ['count'=>$count]);
    }

    /**
     * @deprecated
     */
    public function oTitleAction()
    {
        $this->objectTitleAction();
    }
    /**
     * Get object title
     */
    public function objectTitleAction()
    {
        $object = $this->request->post('object','string', false);
        $id = $this->request->post('id', 'string', false);

        if(!$object || !Orm\Object\Config::configExists($object))
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        if(!in_array(strtolower($object), $this->canViewObjects , true))
            $this->response->error($this->lang->get('CANT_VIEW'));

        $objectConfig = Orm\Object\Config::factory($object);
        // Check ACL permissions
        $acl = $objectConfig->getAcl();
        if($acl){
            if(!$acl->can(Orm\Object\Acl::ACCESS_VIEW , $object)){
                $this->response->error($this->lang->get('ACL_ACCESS_DENIED'));
            }
        }

        try {
            $o = Orm\Object::factory($object, $id);
            $this->response->success(array('title'=>$o->getTitle()));
        }catch (Exception $e){
            Model::factory($object)->logError('Cannot get title for '.$object.':'.$id);
            $this->response->error($this->lang->get('CANT_EXEC'));
        }
    }

    /**
     * Get list of items. Returns JSON reply with
     * ORM object field data or return array with data and count;
     * Filtering, pagination and search are available
     * Sends JSON reply in the result
     * and closes the application (by default).
     * @throws \Exception
     * @return void
     */
    public function listAction()
    {
        if(!$this->eventManager->fireEvent(EventManager::BEFORE_LIST, new \stdClass())){
            $this->response->error($this->eventManager->getError());
        }

        $result = $this->getList();

        $eventData = new \stdClass();
        $eventData->data = $result['data'];
        $eventData->count = $result['count'];

        if(!$this->eventManager->fireEvent(EventManager::AFTER_LIST, $eventData)){
            $this->response->error($this->eventManager->getError());
        }

        $this->response->success(
            $eventData->data,
            ['count'=>$eventData->count]
        );
    }

    /**
     * Prepare data for listAction
     * backward compatibility
     * @return array
     * @throws \Exception
     */
    protected function getList()
    {
        $api = $this->getApi($this->apiRequest, $this->user);
        $count = $api->getCount();

        if(!$count){
            return ['data'=>[],'count'=>0];
        }

        $data = $api->getList();

        if(!empty($this->listLinks)){
            $objectConfig = Orm\Object\Config::factory($this->objectName);
            /**
             * @todo refactor
             */
//
//            if(!in_array($objectConfig->getPrimaryKey(),'',true)){
//                throw new Exception('listLinks requires primary key for object '.$objectConfig->getName());
//            }

            $this->addLinkedInfo($objectConfig, $this->listLinks, $data, $objectConfig->getPrimaryKey());
        }

        return ['data' =>$data , 'count'=> $count];
    }


    /**
     * Create/edit object data
     * The type of operation is defined as per the parameters being transferred
     * Sends JSON reply in the result and
     * closes the application
     */
    public function editAction()
    {
        $id = $this->request->post('id' , 'integer' , false);
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
        $this->checkCanEdit();
        $this->insertObject($this->getPostedData($this->objectName));
    }

    /**
     * Update object data
     * Sends JSON reply in the result and
     * closes the application
     */
    public function updateAction()
    {
        $this->checkCanEdit();
        $this->updateObject($this->getPostedData($this->objectName));
    }

    /**
     * Delete object
     * Sends JSON reply in the result and
     * closes the application
     */
    public function deleteAction()
    {
        $this->checkCanDelete();
        $id = $this->request->post('id' , 'integer' , false);

        if(!$id)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        try{
            $object =  Orm\Object::factory($this->objectName , $id);
        }catch(\Exception $e){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $acl = $object->getAcl();
        if($acl && !$acl->canDelete($object))
            $this->response->error($this->lang->get('CANT_DELETE'));

        if($this->appConfig->get('vc_clear_on_delete'))
            Model::factory('Vc')->removeItemVc($this->objectName , $id);

        if(!$object->delete())
            $this->response->error($this->lang->get('CANT_EXEC'));

        $this->response->success();
    }

    /**
     * Save new ORM object (insert data)
     * Sends JSON reply in the result and
     * closes the application
     * @param Orm\Object $object
     * @return void
     */
    public function insertObject(Orm\Object $object)
    {
        if(!$recId = $object->save())
            $this->response->error($this->lang->get('CANT_EXEC'));

        $this->response->success(['id' => $recId]);
    }

    /**
     * Update ORM object data
     * Sends JSON reply in the result and
     * closes the application
     * @param Orm\Object $object
     */
    public function updateObject(Orm\Object $object)
    {
        if(!$object->save())
            $this->response->error($this->lang->get('CANT_EXEC'));

        $this->response->success(['id' => $object->getId()]);
    }

    /**
     * Get posted data and put it into Orm\Object
     * (in case of failure, JSON error message is sent)
     * @param string $objectName
     * @return Orm\Object
     */
    public function getPostedData($objectName)
    {
        $formCfg = $this->config->get('form');
        $adapterConfig = Config::storage()->get($formCfg['config']);
        $adapterConfig->set('orm_object', $objectName);
        /**
         * @var App\Form\Adapter $form
         */
        $form = new $formCfg['adapter'](
            $this->request,
            $this->lang,
            $adapterConfig
        );

        if(!$form->validateRequest())
        {
            $errors = $form->getErrors();
            $formMessages = [$this->lang->get('FILL_FORM')];
            $fieldMessages = [];
            /**
             * @var App\Form\Error $item
             */
            foreach ($errors as $item)
            {
                $field = $item->getField();
                if(empty($field)){
                    $formMessages[] = $item->getMessage();
                }else{
                    $fieldMessages[$field] = $item->getMessage();
                }
            }
            $this->response->error(implode('; <br>', $formMessages) , $fieldMessages);
        }
        return $form->getData();
    }

}
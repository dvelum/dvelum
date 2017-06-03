<?php
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Config;

class Backend_Vcs_Controller extends Backend_Controller
{
    public function indexAction(){}

    public function listAction()
    {
        $object = Request::post('object', 'string', false);

        if (!$object) {
            Response::jsonSuccess(array());
        }

        $pager = Request::post('pager', 'array', null);
        $filter = Request::post('filter', 'array', null);

        $filter['object_name'] = $object;

        $model = Model::factory('Vc');
        $data = $model->getList($pager, $filter, ['version', 'date', 'id', 'record_id', 'user_id']);

        $objectConfig = Orm\Object\Config::factory('Vc');
        $this->addLinkedInfo($objectConfig, ['user_name' => 'user_id'], $data, $objectConfig->getPrimaryKey());

        $result = array(
            'success' => true,
            'count' => $model->query()->filters($filter)->getCount(),
            'data' => $data
        );

        Response::jsonArray($result);
    }
}
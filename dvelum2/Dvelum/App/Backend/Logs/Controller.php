<?php

use Dvelum\Config;
use Dvelum\Orm;
use Dvelum\Orm\Model;

class Backend_Logs_Controller extends Backend_Controller_Crud
{
	protected  $_canViewObjects = ['User'];


	public function listAction()
	{
		$pager = Request::post('pager', 'array', array());
		$filter = Request::post('filter', 'array', array());

		$history = Model::factory('Historylog');

        if(isset($filter['date']) && !empty($filter['date'])){
            $date = date('Y-m-d' ,strtotime($filter['date']));
            $filter['date'] = new Db_Select_Filter('date',array(
                $date.' 00:00:00', $date.' 23:59:59'
            ),Db_Select_Filter::BETWEEN);
        }

        $data = $history->getList($pager, $filter, ['date','type','id','object','user_id','record_id']);

		if(!empty($data))
		{
            $users = Utils::fetchCol('user_id' , $data);
            $users = Orm\Object::factory('User' , $users);

			foreach ($data as $k=>&$v)
			{
                if(!empty($v['user_id']) && isset($users[$v['user_id']])){
                    $v['user_name'] = $users[$v['user_id']]->getTitle();
                }
                if(!empty($v['object']) && Orm\Object\Config::configExists($v['object'])){
                    $v['object_title'] = Orm\Object\Config::factory($v['object'])->getTitle();
                }
			}unset($v);
		}

		Response::jsonSuccess(
		    $data ,
            [
                'count'=>$history->query()
                         ->filters($filter)
                         ->getCount()
            ]
        );
	}

    /**
     * Get list of registered DB Objects
     */
    public function objectsListAction()
    {
        $manager = new Orm\Object\Manager();
        $list = $manager->getRegisteredObjects();
        $data = [];
        foreach ($list as $object){
            $data[] = ['id'=>$object, 'title' => Orm\Object\Config::factory($object)->getTitle()];
        }
        Response::jsonSuccess($data);
    }

    /**
     * Get changes list
     */
    public function changesListAction()
    {
        $filter = Request::post('filter' , Filter::FILTER_ARRAY , false);

        if(empty($filter['id'])){
            Response::jsonSuccess();
        }

        $id = intval($filter['id']);

        try{
            $rec = Orm\Object::factory('Historylog' , $id);
        }catch (Exception $e){
            Model::factory('Historylog')->logError('Invalid id requested: '.$id);
            Response::jsonSuccess();
        }

        $before = $rec->get('before');
        $after = $rec->get('after');

        if(empty($before) && empty($after)){
            Response::jsonSuccess();
        }
        $before = json_decode($before , true);
        $after = json_decode($after , true);

        $data = [];
        if(!empty($before)){
            foreach($before as $field=>$value){
                $data[$field]['id'] = $field;
                $data[$field]['before'] = $value;
            }
        }
        if(!empty($after)){
            foreach($after as $field=>$value){
                $data[$field]['id'] = $field;
                $data[$field]['after'] = $value;
            }
        }
        Response::jsonSuccess(array_values($data));
    }
}
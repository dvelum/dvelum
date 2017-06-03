<?php
use Dvelum\Orm;
class Backend_User_Auth_Controller extends Backend_Controller_Crud
{
    protected $_listFields = ["user","type","id"];
    protected $_listLinks = ["user"];
    protected $_canViewObjects = ["user"];

    protected function _getList()
    {
        $pager = Request::post('pager' , 'array' , null);
        $filter = Request::post('filter' , 'array' , null);
        $query = Request::post('search' , 'string' , null);
        $filter = array_merge($filter , Request::extFilters());

        $dataModel = Model::factory($this->_objectName);

        $joins = array(
            'user' => array(
                'joinType'=>'joinInner',
                'table'=>array('us'=>Model::factory('user')->table()),
                'condition'=>'user = us.'.Model::factory('user')->getPrimaryKey(),
                'fields'=>array('login'=>'us.login')
            )
        );

        $data = $dataModel->getListVc($pager, $filter, $query, $this->_listFields, false, false, $joins);

        if(empty($data))
            return [];

        if(!empty($this->_listLinks)){
            $objectConfig = Orm\Object\Config::factory($this->_objectName);
            if(!in_array($objectConfig->getPrimaryKey(),$this->_listFields,true)){
            throw new Exception('listLinks requires primary key for object '.$objectConfig->getName());
            }
            $this->addLinkedInfo($objectConfig, $this->_listLinks, $data, $objectConfig->getPrimaryKey());
        }
        return [
            'data' =>$data ,
            'count'=> $dataModel->query()
                                ->filters($filter)
                                ->search($query)
                                ->getCount()
        ];
    }
}
<?php
class Sysdocs_Info
{
    /**
     * Get class info by HID
     * @param string $fileHid
     * @param integer $version
     * @return array
     */
    public function getClassInfoByFileHid($fileHid , $version)
    {
        $classId = Model::factory('sysdocs_class')->getList(array('limit'=>1) , array('fileHid'=>$fileHid,'vers'=>$version) , array('id'));
        if(empty($classId))
            return array();

        return $this->getClassInfo($classId[0]['id']);
    }

    /**
     * Get class info by id
     * @param integer $id
     * @return array
     */
    protected function getClassInfo($id)
    {
        $classModel = Model::factory('sysdocs_class');
        $info =  $classModel->getItem($id);

        if(empty($info))
            return array();

        $result = array(
        				'name' => $info['name'],
        		        'extends' => $info['extends'],
        		        'itemType'=> $info['itemType'],
        		        'abstract' => $info['abstract'],
        		        'deprecated' => $info['deprecated'],
                        'hierarchy' => array(),
        		        'description' => nl2br($info['description']),
                        'properties' => $this->getClassProperties($id),
                        'methods' => $this->getClassMethods($id)
        );

        if(!empty($info['parentId']))
        {
          $tree = $classModel->getTree($info['vers']);
          $parentList  = $tree->getParentsList($id);
          $parentList[] = $id;


          if(!empty($parentList))
          {
            $classObject = new stdClass();
            $classObject->id = $info['id'];
            $classObject->text = $info['name'];
            //$classObject->leaf = true;
            $classObject->expanded = true;
            $classObject->iconCls = 'emptyIcon';

            foreach ($parentList as  $k=>$id)
            {
              $cdata = $tree->getItem($id);

              $object = new stdClass();
              $object->id = $cdata['id'];
              $object->text = $cdata['data'];
             // $object->leaf = true;
              $object->expanded = true;
              $object->iconCls = 'emptyIcon';
              $parentList[$k] = $object;
            }

            $base = false;
            $curObject = false;
            foreach ($parentList as  $item)
            {
            	if(!$base){
            		$base = $item;
            		$curObject = $base;
            		continue;
            	}
            	$tmp = $curObject;
            	$tmp->children = $item;
            	$curObject = $item;
            }
          }
          $result['hierarchy'] = $base;
        }
        return $result;
    }

    /**
     * Get class properties info by class id
     * @param integer $classId
     * @return array
     */
    public function getClassProperties($classId)
    {
      $propModel = Model::factory('sysdocs_class_property');
      $list = $propModel->getList(
          array('sort'=>'name','dir'=>'ASC') ,
          array('classId'=>$classId),
          array(
              'id',
              'const',
              'constValue',
              'deprecated',
              'description',
              'inherited',
              'name',
              'static',
              'type',
              'visibility'
          )
      );

      if(empty($list))
        $list = array();

      foreach ($list as $k=>$v){
      	$list[$k]['description'] = nl2br($v['description']);
      }

      return $list;
    }

    /**
     * Get class methods info by class id
     * @param integer $classId
     * @return array
     */
    public function getClassMethods($classId)
    {
      $propModel = Model::factory('sysdocs_class_method');
      $list = $propModel->getList(
          array('sort'=>'name','dir'=>'ASC') ,
          array('classId'=>$classId),
          array(
              'id',
              'name',
              'deprecated',
              'description',
              'inherited',
              'throws',
              'static',
              'abstract',
              'visibility',
              'returnType',
              'returnsReference',
              'final'
          )
      );

      if(empty($list))
          $list = array();

      $list = Utils::rekey('id', $list);

      foreach ($list as $k=>$v){
      	$list[$k]['description'] = nl2br($v['description']);
      }

      $params = Model::factory('sysdocs_class_method_param')->getList(
        array(
          'sort'=>'index',
          'order'=>'DESC'
        ),
        array(
          'methodId'=>array_keys($list)
        ),
        array(
            'methodId',
            'name',
            'default',
            'isRef',
            'description',
            'optional',
            'id'
        )
      );

      $params = Utils::groupByKey('methodId', $params);

      foreach ($list as $id=>&$data)
      {
        if(isset($params[$data['id']]))
        {
          $data['params'] = $params[$data['id']];
          $pList = Utils::fetchCol('name',  $params[$data['id']]);

          foreach ($pList as &$prop){
            $prop = '$' . $prop;
          }unset($prop);

          $data['paramsList'] = implode(', ', $pList);
        }else{
          $data['params'] = array();
          $data['paramsList'] = '';
        }
      }unset($data);

      return array_values($list);
    }
}
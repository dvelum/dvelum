<?php

class Model_Blocks extends Model
{

  /**
   * Set block mapping for page
   *
   * @param integer $pageId
   * @param array $map
   *          like array(
   *          'code'=>array('blockid1','blockid2'),... or
   *          'code'=>array(array('id'=>'blockid1'),array('id':'blockid2')
   *          'code3'=>array('blockid4','blockid7)
   *          )
   */
  public function setMapping($pageId , array $map)
  {
    $bMapping = Model::factory('Blockmapping');
    $bMapping->clearMap($pageId);

    if(empty($map))
      return true;

    foreach($map as $code => $items){
      $ids = array();
      if(! empty($items)){
        foreach($items as $k => $v){
          if(is_array($v))
            $ids[] = $v['id'];
          else
            $ids[] = $v;
        }
      }

      $bMapping->addBlocks($pageId, $code, $ids);
    }

    return true;
  }

  /**
   * Get block list for page
   *
   * @param integer $page
   * @param integer $version - optional
   * @return array - block list sorted by place code
   */
  public function getPageBlocks($pageId , $version = false)
  {
    if($version)
      return $this->extractBlocks($pageId, $version);

    $bMapping = Model::factory('Blockmapping');

    $sql = $this->_dbSlave->select()
      ->from(array(
            't' => $this->table()
    ))
      ->join(array(
            'map' => $bMapping->table()
    ), 't.id = map.block_id', array(
            'place'
    ));

    if(! $pageId){
      $sql->where('map.page_id  IS NULL');
    }else{
      $sql->where('map.page_id = ' . intval($pageId));
    }
    $sql->order('map.order_no ASC');

    $data = $this->_dbSlave->fetchAll($sql);

    if(! empty($data))
      $data = Utils::groupByKey('place', $data);

    return $data;
  }

  /**
   * Get blocks map from object vesrion
   *
   * @param integer $pageId
   * @param integer $version
   * @return array
   */
  public function extractBlocks($pageId , $version)
  {
    $vcModel = Model::factory('Vc');
    $data = $vcModel->getData('page', $pageId, $version);

    if(! isset($data['blocks']) || empty($data['blocks']))
      return array();

    $data = unserialize($data['blocks']);

    if(empty($data))
      return array();

    $ids = array();
    $info = array();
    foreach($data as $place => $items){
      if(!empty($items)){
        foreach($items as $index => $config)
          $ids[] = $config['id'];

        $sql = $this->_dbSlave->select()
          ->from($this->table())
          ->where('`id` IN(' . Model::listIntegers($ids) . ')');

        $info = $this->_dbSlave->fetchAll($sql);
      }
    }

    if(! empty($info))
      $info = Utils::rekey('id', $info);

    foreach($data as $place => $items){
      if(! empty($items)){
        foreach($items as $index => $config){
          if(isset($info[$config['id']])){
            $data[$place][$index] = $info[$config['id']];
            $data[$place][$index]['place'] = $place;
          }
        }
      }
    }
    return $data;
  }

  /**
   * Get default blocks map
   *
   * @return array
   */
  public function getDefaultBlocks()
  {
    return $this->getPageBlocks(false);
  }
}
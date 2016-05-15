<?php
class Model_Menu extends Model
{

    public function resetCachedMenuLinks($menuId)
    {
        if($this->_cache)
            $this->_cache->remove($this->getCacheKey(array('links' , $menuId)));
    }

    public function getCachedMenuLinks($menuId)
    {

        $menuRecord = $this->getCachedItem($menuId);

        if(! $menuRecord)
            return array();

        $list = false;

        if($this->_cache)
        {
            $cacheKey = $this->getCacheKey(array('links' , $menuId));
            $list = $this->_cache->load($cacheKey);
        }

        if($list !== false)
            return $list;

        $itemModel = Model::factory('Menu_Item');
        $list = $itemModel->getList(
            array(
                'sort' => 'order' ,
                'dir' => 'ASC'
            ) , array(
            'menu_id' => $menuRecord['id']
        ) , array(
                'order' ,
                'page_id' ,
                'published' ,
                'title' ,
                'parent_id' ,
                'tree_id' ,
                'url' ,
                'resource_id' ,
                'link_type'
            )
        );

        if(empty($list))
            return array();

        $this->_addUrls($list);

        if($this->_cache)
            $this->_cache->save($list , $cacheKey);

        return $list;
    }

    protected function _addUrls(& $menuItems)
    {
        $codes = Model::factory('Page')->getCachedCodes();
        $resourceIds = array();
        $resourcesData = array();

        foreach($menuItems as $k => &$v)
        {
            if(isset($codes[$v['page_id']]))

                $v['page_code'] = $codes[$v['page_id']];
            else
                $v['page_code'] = '';

            if($v['link_type'] === 'resource')
                $resourceIds[] = $v['resource_id'];
        }
        unset($v);

        if(! empty($resourceIds))
        {
            $resourceIds = array_unique($resourceIds);
            $data = Model::factory('Medialib')->getItems($resourceIds , array('id' , 'path'));

            if(!empty($data))
                $resourcesData = Utils::rekey('id' , $data);
        }

        foreach($menuItems as $k => &$v)
        {
            $v['link_url'] = '';
            switch($v['link_type'])
            {
                case 'page' :
                    if($v['page_code'] == 'index'){
                        $v['link_url'] = Request::url(['']);
                    }else{
                        $v['link_url'] = Request::url([$v['page_code']]);
                    }
                    break;
                case 'url' :
                    $v['link_url'] = $v['url'];
                    break;
                case 'resource' :
                    if(isset($resourcesData[$v['resource_id']]))
                        $v['link_url'] = Model_Medialib::addWebRoot($resourcesData[$v['resource_id']]['path']);
                    break;
                case 'nolink' :
                        $v['link_url'] = false;
                    break;
            }
        }
        unset($v);
    }
}
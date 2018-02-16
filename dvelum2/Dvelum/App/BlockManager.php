<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *  
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Dvelum\App;

use Dvelum\Orm\Model;
use Dvelum\Config;
use Dvelum\Utils;
use Dvelum\Cache\CacheInterface;

use \Page;

class BlockManager
{
    /**
     * @var bool $hardCache
     */
    protected $hardCache = false;
    /**
     * @var \Cache_Interface|bool  $cache
     */
    protected $cache = false;


    protected $map = [];
    protected $defaultMap = false;
    protected $pageId;
    protected $version;
    protected $hasNoCacheBlock = false;

    const DEFAULT_BLOCK = 'Block_Simple';
    const CACHE_KEY = 'blockmanager_data';

    /**
     * Set cache adapter for the current object
     * @param CacheInterface $cache
     * @return void
     */
    public function setCache(CacheInterface $cache) : void
    {
        $this->cache = $cache;
    }

    /**
     * Disable cache
     * @return void
     */
    public function disableCache() : void
    {
        $this->cache = false;
    }

    /**
     * Use hard cache expiration time from main config for blocks cache
     * It will decrease the block cache lifetime
     * if the number of cache invalidation triggers is not enough
     * @param boolean $flag
     * @return void
     */
    public function useHardCacheTime($flag) : void
    {
        $this->hardCache = $flag;
    }

    /**
     * Initialize blocks
     * @param string $pageId
     * @param boolean - optional, use default blocks map
     * @param boolean - optional, object version
     * @return void
     */
    public function init($pageId , $defaultMap = false , $version = false) : void
    {
        $this->map = array();
        $this->pageId = $pageId;
        $this->version = $version;

        if($defaultMap)
            $this->defaultMap = true;

        $this->loadBlocks();
    }

    /**
     * Load blocks configs and render blocks
     * @return void
     */
    protected function loadBlocks() : void
    {
        $data = false;
        $mapKey = $this->hashMap($this->pageId);

        /*
         * Search for cached HTML of current page and blocks map
         */
        if($this->cache)
        {
            $map = $this->cache->load($mapKey);

            if(is_array($map)) {
                $this->map = $map;
                return;
            }
        }

        /*
         * Get block configs for current page
         */
        if($this->defaultMap)
            $data = $this->getBlocksMap(0);
        else
            $data = $this->getBlocksMap($this->pageId , $this->version);

        $this->map = [];

        if(!empty($data)) {
            /*
             * Render blocks
             */
            foreach($data as $place => $item) {
                $this->map[$place] = '';
                if(!empty($item)){
                    $this->map[$place] = array_map([$this , 'renderBlock'], $item);
                }
            }
        }

        if($this->cache){
            /*
             * Cache rendered HTML
             */
            if(!$this->hasNoCacheBlock)
            {
                if($this->hardCache) {
                    $this->cache->save($this->map , $mapKey , Config::storage()->get('orm.php')->get('hard_cache'));
                } else {
                    $this->cache->save($this->map , $mapKey);
                }
            }
            else
            {
                $this->cache->remove($mapKey);
            }
        }
    }

    /**
     * Get blocks map config
     * @param string $mapPageId
     * @param integer | boolean  $version, optional default false
     * @return  array
     */
    protected function getBlocksMap($mapPageId , $version = false) : array
    {
        $data = false;
        $pageKey = $this->hashPage($this->pageId);

        if($this->cache){
            $data = $this->cache->load($pageKey);
            if($data!==false)
                return $data;
        }
        /**
         * @var \Model_Blocks $blocksModel
         */
        $blocksModel = Model::factory('Blocks');

        $data = $blocksModel->getPageBlocks($mapPageId , $version);

        if(!$data)
            $data = [];

        if($this->cache)
            $this->cache->save($data , $pageKey);

        return $data;
    }

    /**
     * Get cache key for a block as per the class and configuration settings
     * @param string $class - block class
     * @param array $config - block config
     * @return string
     */
    public function getCacheKey($class , array $config) : string
    {
        if(isset($config['place']))
            unset($config['place']);
        return md5('block_data' . $class . serialize($config));
    }

    /**
     * Init and render Block object
     * @param array $config
     * @return string
     */
    protected function renderBlock(array $config)
    {
        $class = self::DEFAULT_BLOCK;

        if($config['is_system'] && strlen($config['sys_name']) && class_exists($config['sys_name']))
            $class = $config['sys_name'];

        /*
         * Check for rendered block cache 
         */
        if($this->cache)
        {
            if($class::cacheable)
            {
                if($class::dependsOnPage)
                    $config['page_id'] = Page::getInstance()->id;

                $cacheKey = $this->getCacheKey($class , $config);
                $data = $this->cache->load($cacheKey);
                if($data)
                    return $data;
            }
            else
            {
                $this->hasNoCacheBlock = true;
            }
        }

        $blockObject = new $class($config);

        if(!($blockObject instanceof \Block) && !($blockObject instanceof \Dvelum\App\Block\AbstractAdapter))
            trigger_error('Invalid block class');

        $html = $blockObject->render();

        if($class::cacheable && $this->cache)
        {
            if($this->hardCache) {
                $this->cache->save($html , $cacheKey , Config::storage()->get('orm.php')->get('hard_cache'));
            } else {
                $this->cache->save($html , $cacheKey);
            }
        }
        return $html;
    }

    /**
     * Get the HTML code generated by blocks for a
     * placeholder specified in the block map configuration
     * @param string $placeCode
     * @return string
     */
    public function getBlocksHtml($placeCode) : string
    {
        if(!isset($this->map[$placeCode]))
            return '';

        if(is_array($this->map[$placeCode]))
            return implode("\n" , $this->map[$placeCode]);
        else
            return '';
    }

    /**
     * Check the designated blocks
     * @param string $placeCode
     * @return bool
     */
    public function hasBlocks($placeCode) : bool
    {
        if(isset($this->map[$placeCode]) && is_array($this->map[$placeCode])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get cache key for the blocks located on the page
     * A system method
     * @param integer $id
     * @return string
     */
    public function hashPage($id) : string
    {
        return self::CACHE_KEY . '_' . $id;
    }

    /**
     * Get cache key for page
     * @param integer $id
     * @return string
     */
    protected function hashMap($id) : string
    {
        return self::CACHE_KEY . 'map_' . $id;
    }

    /**
     * Invalidate block cache by block class (for all pages)
     * @param string $blockClass
     * @return void
     */
    public function invalidateCacheBlockClass($blockClass) : void
    {
        if(!$this->cache)
            return;

        $blockModel = Model::factory('Blocks');
        $blockItems = $blockModel->query()->filters([
            'is_system' => 1 ,
            'sys_name' => $blockClass
        ])->fetchAll();
        $this->invalidateBlockList($blockItems);
    }

    /**
     * Invalidate block cache by block id (for all pages)
     * @param integer $blockId
     * @return void
     */
    public function invalidateCacheBlockId($blockId) : void
    {
        if(!$this->cache)
            return;

        $blockModel = Model::factory('Blocks');
        $blockItems = $blockModel->query()->filters(['id' => $blockId])->fetchAll();
        $this->invalidateBlockList($blockItems);
    }

    /**
     * Invalidate cache by menu id (for all pages)
     * @param integer $menuId
     * @return void
     */
    public function invalidateCacheBlockMenu($menuId) : void
    {
        if(!$this->cache)
            return;

        $blockModel = Model::factory('Blocks');
        $blockItems = $blockModel->query()->filters([
            'menu_id' => $menuId ,
            'is_menu' => 1
        ])->fetchAll();

        $this->invalidateBlockList($blockItems);
    }

    /**
     * Invalidate cache for default block map (for all pages)
     * @return void
     */
    public function invalidateDefaultMap() : void
    {
        if(!$this->cache)
            return;

        /**
         * @var \Model_Page $pagesModel
         */
        $pagesModel = Model::factory('Page');
        $ids = $pagesModel->getPagesWithDefaultMap();

        if(empty($ids))
            return;

        foreach($ids as $id)
        {
            $this->cache->remove($this->hashMap($id));
            $this->cache->remove($this->hashPage($id));
        }
    }

    /**
     * Invalidate cache for page blocks
     * @param integer $pageId
     * @return void
     */
    public function invalidatePageMap($pageId) : void
    {
        if(!$this->cache)
            return;

        $mapKey = $this->hashMap($pageId);
        $pageKey = $this->hashPage($pageId);

        $this->cache->remove($mapKey);
        $this->cache->remove($pageKey);
    }

    /**
     * @param array $blockItems
     * @return void
     */
    protected function invalidateBlockList(array $blockItems) : void
    {
        if(!$this->cache)
            return;

        if(empty($blockItems))
            return;

        $blockIds = Utils::fetchCol('id' , $blockItems);
        $blockMapping = Model::factory('Blockmapping');

        $pageBlocks = $blockMapping->getList(
            false ,
            array(
                'block_id' => $blockIds
            ) ,
            array(
                'page_id' ,
                'block_id'
            )
        );

        if(empty($pageBlocks))
            return;

        /*
         * Reset block config for pages
         */
        $pages = array_unique(Utils::fetchCol('page_id' , $pageBlocks));

        foreach($pages as $id)
        {
            if($id == 0 || empty($id))
                $this->invalidateDefaultMap();

            $this->cache->remove($this->hashPage($id));
            $this->cache->remove($this->hashMap($id));
        }
        unset($pages);

        $sortedPageBlocks = Utils::groupByKey('block_id' , $pageBlocks);
        /**
         * @var \Model_Page $pagesModel
         */
        $pagesModel = Model::factory('Page');
        $defaultMapped = $pagesModel->getPagesWithDefaultMap();

        /*
         * Reset cache for all blocks with current menu on all pages
         */
        foreach($blockItems as $v)
        {

            if(!strlen($v['sys_name']))
            {
                $this->cache->remove($this->getCacheKey(self::DEFAULT_BLOCK , $v));
                continue;
            }

            if(!isset($sortedPageBlocks[$v['id']]) || empty($sortedPageBlocks[$v['id']]))
                continue;

            foreach($sortedPageBlocks[$v['id']] as $pageToBlock)
            {
                if($v['sys_name']::dependsOnPage)
                {
                    $v['page_id'] = $pageToBlock['page_id'];

                    if($v['page_id'] == 0)
                    {
                        foreach($defaultMapped as $pId)
                        {
                            $cfg = $v;
                            $cfg['page_id'] = $pId;
                            $this->cache->remove($this->getCacheKey($v['sys_name'] , $cfg));
                        }
                    }
                }
                $this->cache->remove($this->getCacheKey($v['sys_name'] , $v));
            }
        }
    }
}
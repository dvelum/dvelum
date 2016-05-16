<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2012  Kirill A Egorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Block manager component.
 * Serves for managing blocks of page content,
 * uploading classes, rendering, caching , 
 * as well as for block cache invalidation.
 * @author Kirill A Egorov
 * @license General Public License version 3
 */
class Blockmanager
{
	protected static $_useHardCacheLifetime = false;
	protected static $_defaultCache = false;
	protected static $_instance;
	protected $_map = array();
	protected $_defaultMap = false;
	protected $_pageId;
	protected $_version;
	protected $_hasNoCacheBblock = false;
	
	/**
	 * @var Cache_Interface
	 */
	protected $_cache = false;
	const DEFAULT_BLOCK = 'Block_Simple';
	const CACHE_KEY = 'blockmanager_data';

	/**
	 * Set default cache adapter
	 * @param Cache_Interface $cache
	 */
	static public function setDefaultCache(Cache_Interface $cache)
	{
		self::$_defaultCache = $cache;
	}

	public function __construct()
	{
		$this->_cache = self::$_defaultCache;
	}

	/**
	 *  Set cache adapter for the current object
	 * @param Cache_Interface $cache
	 */
	public function setCache(Cache_Interface $cache)
	{
		$this->_cahe = $cache;
	}

	/**
	 * Disable cache
	 */
	public function disableCache()
	{
		$this->_cache = false;
	}

	/**
	 * Use hard cache expiration time from main config for blocks cache
     * It will decrease the block cache lifetime
     * if the number of cache invalidation triggers is not enough
	 * @param boolean $flag
	 */
	static public function useHardCacheTime($flag)
	{
		self::$_useHardCacheLifetime = $flag;
	}

	/**
	 * Initialize blocks
	 * @param string $pageId
	 * @param boolean - optional, use default blocks map
	 * @param boolean - optional, object version
	 * @return void
	 */
	public function init($pageId , $defaultMap = false , $version = false)
	{
		$this->_map = array();
		$this->_pageId = $pageId;
		$this->_version = $version;
		
		if($defaultMap)
			$this->_defaultMap = true;
		
		$this->_loadBlocks();
	}

	/**
	 * Load blocks configs and render blocks
	 * @return void
	 */
	protected function _loadBlocks()
	{
		$data = false;
		$mapKey = $this->_hashMap($this->_pageId);
		
		/*
		 * Search for cached HTML of current page and blocks map
		 */
		if($this->_cache)
		{
			$map = $this->_cache->load($mapKey);
			
			if(is_array($map))
			{
				$this->_map = $map;
				return;
			}
		}
		
		/*
	     * Get block configs for current page
	     */
		if($this->_defaultMap)
			$data = $this->_getBlocksMap(0);
		else
			$data = $this->_getBlocksMap($this->_pageId , $this->_version);
		
		if(!empty($data))
		{
			/*
			 * Render blocks
			 */
			foreach($data as $place => $item)
			{
				$this->_map[$place] = '';
				if(!empty($item))
					$this->_map[$place] = array_map(array($this , '_renderBlock') , $item);
			}
		}
		else
		{
			$this->_map = array();
		}
		/*
		 * Save rendered HTML
		 */
		if($this->_cache)
		{
			if(!$this->_hasNoCacheBblock)
			{
				if(self::$_useHardCacheLifetime)
				{
					$this->_cache->save($this->_map , $mapKey , Registry::get('main' , 'config')->get('frontend_hardcache'));
				}
				else
				{
					$this->_cache->save($this->_map , $mapKey);
				}
			}
			else
			{
				$this->_cache->remove($mapKey);
			}
		}
	}

	/**
	 * @param string $mapPageId
	 * @param integer | boolean  $version, optional default false
	 */
	protected function _getBlocksMap($mapPageId , $version = false)
	{
		$data = false;
		$pageKey = $this->hashPage($this->_pageId);
		
		if($this->_cache){
		    $data = $this->_cache->load($pageKey);
		    if($data!==false)
		      return $data;
		}
		$blocksModel = Model::factory('Blocks');
		
		$data = $blocksModel->getPageBlocks($mapPageId , $version);
		
		if(!$data)
			$data = array();
		
		if($this->_cache)
			$this->_cache->save($data , $pageKey);
		
		return $data;
	}

	/**
	 * Get cache key for a block as per the class and configuration settings
	 * @param string $class - block class
	 * @param array $config - block config
	 * @return string
	 */
	public function getCacheKey($class , array $config)
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
	protected function _renderBlock(array $config)
	{
		$class = self::DEFAULT_BLOCK;
		
		if($config['is_system'] && strlen($config['sys_name']) && class_exists($config['sys_name']))
			$class = $config['sys_name'];
			
		/*
		 * Check for rendered block cache 
		 */
		if($this->_cache)
		{
			if($class::cacheable)
			{
				if($class::dependsOnPage)
					$config['page_id'] = Page::getInstance()->id;
				
				$cacheKey = $this->getCacheKey($class , $config);
				$data = $this->_cache->load($cacheKey);
				if($data)
					return $data;
			}
			else
			{
				$this->_hasNoCacheBblock = true;
			}
		}
		
		$blockObject = new $class($config);
		
		if(!$blockObject instanceof Block)
			trigger_error('Invalid block class');
		
		$html = $blockObject->render();
		
		if($class::cacheable && $this->_cache)
		{
			if(self::$_useHardCacheLifetime)
			{
				$this->_cache->save($html , $cacheKey , Registry::get('main' , 'config')->get('frontend_hardcache'));
			}
			else
			{
				$this->_cache->save($html , $cacheKey);
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
	public function getBlocksHtml($placeCode)
	{
		if(!isset($this->_map[$placeCode]))
			return '';
		
		if(is_array($this->_map[$placeCode]))
			return implode("\n" , $this->_map[$placeCode]);
		else
			return '';
	}

	/**
	 * Check the designated blocks
	 * @param string $placeCode
	 * @return bool
	 */
	public function hasBlocks($placeCode)
	{
		if(isset($this->_map[$placeCode]) && is_array($this->_map[$placeCode])){
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
	public function hashPage($id)
	{
		return self::CACHE_KEY . '_' . $id;
	}

	/**
	 * Get cache key for page
	 * @param integer $id
	 * @return string
	 */
	protected function _hashMap($id)
	{
		return self::CACHE_KEY . '_map_' . $id;
	}

	/**
	 * Invalidate block cache by block class (for all pages)
	 * @param string $blockClass
	 */
	public function invalidateCacheBlockClass($blockClass)
	{
		if(!$this->_cache)
			return;
		
		$blockModel = Model::factory('Blocks');
		$blockItems = $blockModel->getList(false , array(
				'is_system' => 1 , 
				'sys_name' => $blockClass
		));
		$this->_invalidateBlockList($blockItems);
	}

	/**
	 * Invalidate block cache by block id (for all pages)
	 * @param integer $blockId
	 */
	public function invalidateCacheBlockId($blockId)
	{
		if(!$this->_cache)
			return;
		
		$blockModel = Model::factory('Blocks');
		$blockItems = $blockModel->getList(false , array('id' => $blockId));
		$this->_invalidateBlockList($blockItems);
	}

	/**
	 * Invalidate cache by menu id (for all pages)
	 * @param integer $menuId
	 */
	public function invalidateCacheBlockMenu($menuId)
	{
		if(!$this->_cache)
			return;

		$blockModel = Model::factory('Blocks');
		$blockItems = $blockModel->getList(false , array('menu_id' => $menuId , 'is_menu' => 1));
		
		$this->_invalidateBlockList($blockItems);
	}

	/**
	 * Invalidate cache for default block map (for all pages)
	 */
	public function invalidateDefaultMap()
	{
		if(!$this->_cache)
			return;
		
		$pagesModel = Model::factory('Page');
		$ids = $pagesModel->getPagesWithDefaultMap();
		
		if(empty($ids))
			return;
		
		foreach($ids as $id)
		{
			$this->_cache->remove($this->_hashMap($id));
			$this->_cache->remove($this->hashPage($id));
		}
	}

	/**
	 * Invalidate cache for page blocks
	 * @param integer $pageId
	 */
	public function invalidatePageMap($pageId)
	{
		if(!$this->_cache)
			return;
		
		$mapKey = $this->_hashMap($pageId);
		$pageKey = $this->hashPage($pageId);
		
		$this->_cache->remove($mapKey);
		$this->_cache->remove($pageKey);
	}

	/**
	 * @param array $blockItems
	 */
	protected function _invalidateBlockList(array $blockItems)
	{
		if(!$this->_cache)
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
			
			$this->_cache->remove($this->hashPage($id));
			$this->_cache->remove($this->_hashMap($id));
		}
		unset($pages);
		
		$sortedPageBlocks = Utils::groupByKey('block_id' , $pageBlocks);
		$pagesModel = Model::factory('Page');
		$defaultMapped = $pagesModel->getPagesWithDefaultMap();
		
		/*
		 * Reset cache for all blocks with current menu on all pages
		 */
		foreach($blockItems as $v)
		{
			
			if(!strlen($v['sys_name']))
			{
				$this->_cache->remove($this->getCacheKey(self::DEFAULT_BLOCK , $v));
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
							$this->_cache->remove($this->getCacheKey($v['sys_name'] , $cfg));
						}
					}
				}
				$this->_cache->remove($this->getCacheKey($v['sys_name'] , $v));
			}
		}
	}
}
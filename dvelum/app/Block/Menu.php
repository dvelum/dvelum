<?php

use Dvelum\View;
use Dvelum\Orm\Model;

/**
 * Menu block
 * @author Kirill A Egorov 2011 DVelum project
 */
class Block_Menu extends Block
{
	protected $_template = 'menu.php';
	
	protected $data = [];
	
	const cacheable = true;
	const dependsOnPage = true;
	
	const CACHE_KEY = 'block_menu_';
	
	
	static public function getCacheKey($id){
		return md5(self::CACHE_KEY . '_' . $id);
	}
	
	protected function _collectData()
	{
		return Model::factory('Menu')->getCachedMenuLinks($this->_config['menu_id']);
	}
	/**
	 * (non-PHPdoc)
	 * @see Block_Abstract::render()
	 */
	public function render()
	{
		$data = $this->_collectData();

		$tpl = View::factory();
        $tpl->setData(array(
            'config' => $this->_config,
            'place' => $this->_config['place'],
            'menuData' => $data
        ));

        if(static::dependsOnPage){
            $tpl->set('page' , \Page::getInstance());
            $tpl->set('pagesTree' , Model::factory('Page')->getTree());
        }
        return $tpl->render('public/'. $this->_template);
	}
}
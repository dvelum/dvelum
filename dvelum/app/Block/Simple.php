<?php
/**
 * Simple block
 * @author Kirill A Egorov 2011 DVelum project
 */
class Block_Simple extends Block
{
	const cacheable = true;	
	const CACHE_KEY = 'block_simple';
	/**
	 * (non-PHPdoc)
	 * @see Block_Abstract::render()
	 */
	public function render()
	{
		$tpl = new Template();
		$tpl->set('data' , $this->_config);
		return $tpl->render('public/'. $this->_template);
	}
}
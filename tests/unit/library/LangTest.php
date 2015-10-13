<?php
class LangTest extends PHPUnit_Framework_TestCase
{
	public function testGet()
	{
		
		$enDict = Lang::storage()->get('en.php');
		$ruDict = Lang::storage()->get('ru.php');
		
		Lang::addDictionary('en', $enDict);
		Lang::addDictionary('ru', $ruDict);
		Lang::setDefaultDictionary('en');
		
		$lang = Lang::lang();
		
		$this->assertEquals($lang->ACTION , 'Action');
		$this->assertEquals($lang->get('ACTION') , 'Action');

		$lang = Lang::lang('ru');
		$this->assertEquals($lang->ACTION , 'Действие');
		$this->assertEquals($lang->get('ACTION') , 'Действие');
		
	}
}
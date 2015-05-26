<?php
class LangTest extends PHPUnit_Framework_TestCase
{
	public function testGet()
	{
		
		$enDict = Config::factory(Config::File_Array, Registry::get('main','config')->get('lang_path').'en.php');	
		$ruDict = Config::factory(Config::File_Array, Registry::get('main','config')->get('lang_path').'ru.php');
		
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
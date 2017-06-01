<?php
use PHPUnit\Framework\TestCase;

class LangTest extends TestCase
{
	public function testGet()
	{
        /**
         * @var \Dvelum\Lang
         */
	    $langService = \Dvelum\Service::get('lang');
		
		$enDict = Lang::storage()->get('en.php');
		$ruDict = Lang::storage()->get('ru.php');

        $langService->addDictionary('en', $enDict);
        $langService->addDictionary('ru', $ruDict);
        $langService->setDefaultDictionary('en');
		
		$lang = Lang::lang();
		
		$this->assertEquals($lang->ACTION , 'Action');
		$this->assertEquals($lang->get('ACTION') , 'Action');

		$lang = Lang::lang('ru');
		$this->assertEquals($lang->ACTION , 'Действие');
		$this->assertEquals($lang->get('ACTION') , 'Действие');
		
	}
}
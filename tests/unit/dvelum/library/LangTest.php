<?php
use PHPUnit\Framework\TestCase;

use Dvelum\Lang;
use Dvelum\Config;

class LangTest extends TestCase
{
	public function testGet()
	{
	    $appConfig = \Dvelum\Config::storage()->get('main.php');
        /**
         * @var \Dvelum\Lang
         */
        $langService = \Dvelum\Service::get('lang');

		$enDict = new Lang\Dictionary('en',[
		    'type' => Config\Factory::File_Array,
            'src'  => 'en.php'
        ]);
		$ruDict = new Lang\Dictionary('ru',[
            'type' => Config\Factory::File_Array,
            'src'  => 'ru.php'
        ]);

        $langService->addDictionary('en', $enDict);
        $langService->addDictionary('ru', $ruDict);
        $langService->setDefaultDictionary('en');
		
		$lang = Lang::lang();
		

		$this->assertEquals($lang->get('ACTION') , 'Action');

		$lang = Lang::lang('ru');
		$this->assertEquals($lang->get('ACTION') , 'Действие');
		
	}
}
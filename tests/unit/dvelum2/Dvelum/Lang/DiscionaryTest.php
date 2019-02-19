<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2019  Kirill Yegorov
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
namespace Dvelum\Lang;

use Dvelum\Lang;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testGet()
    {
        $appConfig = \Dvelum\Config::storage()->get('main.php');
        /**
         * @var \Dvelum\Lang
         */
        $langService = \Dvelum\Service::get('lang');

        $ruDict = new Lang\Dictionary('ru',[
            'type' => \Dvelum\Config\Factory::File_Array,
            'src'  => 'ru.php'
        ]);


        $langService->addDictionary('ru', $ruDict);
        $langService->setDefaultDictionary('ru');

        $lang = Lang::lang('ru');
        $this->assertEquals('Да', $lang->get('YES'));
        $this->assertEquals('Да', $lang->YES);
        $this->assertTrue($lang->__isset('YES'));
        $this->assertFalse($lang->__isset('undefined_key'));
        $this->assertEquals('[undefined_key]', $lang->get('undefined_key'));
        $this->assertTrue(!empty(json_decode($lang->getJson(), true)));
        $this->assertEquals('ru', $lang->getName());
    }
}
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
namespace Dvelum;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{

    public function testStorage()
    {
        $cfg = Config::storage()->get('main.php');
        $this->assertTrue($cfg instanceof  Config\ConfigInterface);
    }

    public function testFactory()
    {
        $newConfig = Config::factory(Config\Factory::Simple,'name');
        $this->assertTrue($newConfig instanceof  Config\ConfigInterface);
    }
}
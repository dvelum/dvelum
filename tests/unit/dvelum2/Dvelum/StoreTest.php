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

use Dvelum\Store\AdapterInterface;
use PHPUnit\Framework\TestCase;

use \Dvelum\Store\Factory;

class StoreTest extends TestCase
{
	public function testFactory()
	{
		$this->assertInstanceOf('\Dvelum\Store\AdapterInterface' , Factory::get());
		$this->assertInstanceOf('\Dvelum\Store\Local' , Factory::get(Factory::LOCAL));
		$this->assertInstanceOf('\Dvelum\Store\Session' , Factory::get(Factory::SESSION));
	}
}
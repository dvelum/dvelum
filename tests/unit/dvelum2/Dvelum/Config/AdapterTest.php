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
namespace Dvelum\Config;

use Dvelum\Config;
use PHPUnit\Framework\TestCase;

class AdapterTest extends TestCase
{

    protected function createConfig(string $name) : ConfigInterface
    {
        return  Config\Factory::config(Config\Factory::Simple, $name);
    }

    public function testGetCount()
    {
        $config = $this->createConfig('test_cfg');
        $config->setData([
            'key1'=>1,
            'key2'=>2
        ]);
        $this->assertEquals(2, $config->getCount());
        $this->assertEquals('test_cfg', $config->getName());
    }

    public function testRemove()
    {
        $config = $this->createConfig('test_cfg2');
        $config->setData([
            'key1'=>1,
            'key2'=>2
        ]);
        $config->remove('key1');
        $this->assertFalse($config->offsetExists('key1'));
        $config->removeAll();
        $this->assertEquals(0, $config->getCount());
    }

    public function testIteratorAccess()
    {
        $config = $this->createConfig('test_cfg3');
        $config->setData([
            'key1'=>1,
            'key2'=>2
        ]);
        foreach ($config as $index => $item)
        {
            $this->assertEquals($index, 'key'.$item);
        }
    }

    public function testArrayAccess()
    {
        $config = $this->createConfig('test_cfg4');
        $config->setData([
            'key1'=>1,
            'key2'=>2
        ]);
        $this->assertEquals(1, $config['key1']);
        unset($config['key1']);
        $this->assertTrue(!$config->offsetExists('key1'));
        $this->assertTrue(isset($config['key2']));
    }

    public function testSetParentId()
    {
        $config = $this->createConfig('test_cfg4');
        $config->setParentId('main_test.php');
        $this->assertEquals('main_test.php', $config->getParentId());
    }
}
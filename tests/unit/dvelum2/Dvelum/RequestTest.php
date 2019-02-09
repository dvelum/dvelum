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
namespace Dvelum\Utils;

use Dvelum\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testGetPart()
    {
        $request = Request::factory();
        $request->setUri('/test/request/uri');
        $this->assertEquals('/test/request/uri', $request->getUri());
        $this->assertEquals('test', $request->getPart(0));
        $this->assertEquals('request', $request->getPart(1));
        $this->assertEquals('uri', $request->getPart(2));
    }

    public function testGet()
    {
        $request = Request::factory();
        $_GET['param'] = 'value';
        $request->updateGet('param','value');
        $request->updateGet('param3','1');
        $this->assertEquals('value', $request->get('param','string',''));
        $this->assertEquals(null, $request->get('param2','string',null));
        $this->assertEquals(1, $request->get('param3','int',null));
    }

    public function testPost()
    {
        $request = Request::factory();
        $request->updatePost('param','value');
        $request->updatePost('param3','1');
        $this->assertEquals('value', $request->post('param','string',''));
        $this->assertEquals(null, $request->post('param2','string',null));
        $this->assertEquals(1, $request->post('param3','int',null));
        $this->assertTrue($request->hasPost());
    }

    public function testSetPost()
    {
        $request = Request::factory();
        $request->setPostParams(['param1'=>'val1','param2'=>'val2']);
        $this->assertEquals(['param1'=>'val1','param2'=>'val2'],$request->postArray());
    }

    public function testSetGet()
    {
        $request = Request::factory();
        $request->setGetParams(['param'=>'val1','param2'=>'val2']);
        $this->assertEquals(['param'=>'val1','param2'=>'val2'],$request->getArray());
    }
}

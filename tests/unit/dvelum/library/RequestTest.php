<?php
use PHPUnit\Framework\TestCase;
use Dvelum\Request;

class RequestTest extends TestCase
{
	public function testSetUri()
	{
	    $request = Request::factory();
	    $request->setUri('/news.html?a=b&d=8345');
	    $this->assertEquals($request->getUri(),'/news');
	}
	
	public function testGetArray()
	{
        $request = Request::factory();
        $request->updateGet('key', 'val');
	    $this->assertEquals($request->getArray(), array('key'=>'val'));
	}
	
	public function testPostArray()
	{
        $request = Request::factory();
        $request->updatePost('key', 'val');
	    $this->assertEquals($request->postArray(), array('key'=>'val'));
	}
	
	public function testUpdatePost()
	{
        $request = Request::factory();
        $request->updatePost('key', 'val');
	    $this->assertEquals($request->post('key', 'string' , false),'val');
	    $this->assertEquals($request->post('key3', 'string' , false), false);
	}
	
	public function testUpdateGet()
	{
        $request = Request::factory();
        $request->updateGet('key', 'val');
	    $this->assertEquals($request->get('key', 'string' , false),'val');
	    $this->assertEquals($request->get('key3', 'string' , false), false);
	}	
	
	public function testIsAjax()
	{
        $request = Request::factory();
	    $this->assertEquals($request->isAjax(), false);
	    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
	    $this->assertEquals($request->isAjax(), true);
	}
	
	public function testHasPost()
	{
	    $request = Request::factory();
	    $post = $request->postArray();
	    $this->assertEquals(!empty($post), $request->hasPost());
	    
	    $request->updatePost('key', 'value');
	    
	    $post = $request->postArray();
	    $this->assertEquals(!empty($post), $request->hasPost());	    
	}
	
	
	public function testGetPart()
	{
        $request = Request::factory();
        $request->setUri('/news/item/1');
	    $this->assertEquals($request->getPart(0) , 'news');
	    $this->assertEquals($request->getPart(1) , 'item');
	    $this->assertEquals($request->getPart(2) , '1');
	}
}
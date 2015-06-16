<?php
class RequestTest extends PHPUnit_Framework_TestCase
{
	public function testSetUri()
	{
	    $request = Request::getInstance();
	    $request->setUri('/news.html?a=b&d=8345');
	    $this->assertEquals($request->getUri(),'/news');
	}
	
	public function testGetArray()
	{
	    Request::updateGet('key', 'val');
	    $this->assertEquals(Request::getArray(), array('key'=>'val'));    
	}
	
	public function testPostArray()
	{
	    Request::updatePost('key', 'val');
	    $this->assertEquals(Request::postArray(), array('key'=>'val'));
	}
	
	public function testUpdatePost()
	{
	    Request::updatePost('key', 'val');
	    $this->assertEquals(Request::post('key', 'string' , false),'val');
	    $this->assertEquals(Request::post('key3', 'string' , false), false);
	}
	
	public function testUpdateGet()
	{
	    Request::updateGet('key', 'val');
	    $this->assertEquals(Request::get('key', 'string' , false),'val');
	    $this->assertEquals(Request::get('key3', 'string' , false), false);
	}	
	
	public function testIsAjax()
	{
	    $this->assertEquals(Request::isAjax(), false);
	    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
	    $this->assertEquals(Request::isAjax(), true);
	}
	
	public function testHasPost()
	{
	    $request = Request::getInstance();
	    $post = $request->postArray();
	    $this->assertEquals(!empty($post), $request->hasPost());
	    
	    $request->updatePost('key', 'value');
	    
	    $post = $request->postArray();
	    $this->assertEquals(!empty($post), $request->hasPost());	    
	}
	
	
	public function testGetPart()
	{
	    Request::setDelimiter('/');
	    $r = Request::getInstance();
	    $r->setUri('/news/item/1');
	    
	    $this->assertEquals($r->getPart(0) , 'news');
	    $this->assertEquals($r->getPart(1) , 'item');
	    $this->assertEquals($r->getPart(2) , '1');
	       
	}
	
	public function testGetSpecialPart()
	{
	    Request::setDelimiter('-');
	    $r = Request::getInstance();
	    $r->setUri('/news-item-1');
	    $this->assertEquals($r->getPart(0) , 'news');
	    $this->assertEquals($r->getPart(1) , 'item');
	    $this->assertEquals($r->getPart(2) , '1');
	}
}
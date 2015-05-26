<?php
class TreeTest extends PHPUnit_Framework_TestCase
{

	public function testSetItemOrder()
	{
		$tree = new Tree();
		$tree->addItem(1, 0 , 'item1');
		$tree->setItemOrder(1,2);
		
		$item = $tree->getItem(1);
		$this->assertEquals($item['order'] , 2);
	}
	/**
     * @depends test_sortChilds
     */
	public function testSortItems()
	{
		
	}


	public function testItemExists()
	{
		$tree = new Tree();
		$item = new stdClass();
		$tree->addItem(1, 0 , 'item1');
		
		$this->assertTrue($tree->itemExists(1));
		$tree->addItem('asd', 0 , 'item1');
		$this->assertFalse($tree->itemExists(2));
	}


	public function testGetItemsCount()
	{	
		$tree = new Tree();
		$item = new stdClass();
		$tree->addItem(1, 0 , 'item1');
		$this->assertEquals($tree->getItemsCount() , 1);
		$tree->addItem(2, 1 , 100);
		$this->assertEquals($tree->getItemsCount() , 2);
	}


	public function testAddItem()
	{
		$tree = new Tree();
		$item = new stdClass();
		$item->id = 1;
		
		$this->assertTrue($tree->addItem($item->id, 0 , $item));
		$this->assertEquals(1 , $tree->getItemsCount());
		
		$item = new stdClass();
		$item->id = 0;
		$this->assertFalse($tree->addItem($item->id, 0 , $item));
		$this->assertEquals(1 , $tree->getItemsCount());
		
		$item->id = 2;
		$this->assertTrue($tree->addItem($item->id, 1 , $item) , true);
		$this->assertEquals(2 , $tree->getItemsCount());
		
	}


	public function testUpdateItem()
	{
		$tree = new Tree();
		$item = new stdClass();
		$item->id = 1;
		$tree->addItem($item->id, 0 , $item);
		
		$item2 = array('id'=>2,'text'=>'text');
		
		$this->assertTrue($tree->updateItem($item->id, $item2));
		$this->assertFalse($tree->updateItem(4, $item2));
		
		$this->assertEquals($tree->getItemData($item->id) ,$item2);
		
	}

	public function testGetItem()
	{
		$tree = new Tree();
		$item = new stdClass();
		$item->id = 1;
		$tree->addItem($item->id, 0 , $item);
		
		$itemResult = $tree->getItem($item->id);
		$this->assertTrue(!empty($itemResult));
		$this->assertTrue(is_array($itemResult));
		$this->assertEquals($itemResult['id'], $item->id);
		$this->assertEquals($itemResult['parent'],0);
		$this->assertEquals($itemResult['data'] , $item);
		$this->assertEquals($itemResult['order'] , 0);
		
		$exception = false;
		try{
			$tree->getItem(8);
		}catch (Exception $e){
			$exception = true;
		}
		
		$this->assertTrue($exception);
	}
	/**
     * @depends testGetItem
     */
	public function testGetItemData()
	{
		$tree = new Tree();
		$item = new stdClass();
		$item->id = 1;
		$tree->addItem($item->id, 0 , $item);
		$this->assertEquals( $tree->getItemData($item->id) , $item);
	}

	public function testHasChilds()
	{
		$tree = new Tree();
		$item = new stdClass();
		$item->id = 1;
		$tree->addItem($item->id, 0 , $item);
		
		$item2 = new stdClass();
		$item2->id = 2;
		$tree->addItem($item2->id, 1 , $item2);
		
		$this->assertTrue($tree->hasChilds(0));
		$this->assertTrue($tree->hasChilds(1));
		$this->assertFalse($tree->hasChilds(2));
	}

	public function testGetChildsR()
	{		
		$tree = new Tree();
		$tree->addItem(1, 0 , 100);
		$tree->addItem(2, 1 , 200);
		$tree->addItem(3, 2 , 300);
		$tree->addItem(4, 3 , 400);
		$tree->addItem(5, 1 , 500);
		
		$childs = $tree->getChildsR(1);
			
		$this->assertContains(2 ,$childs);
		$this->assertContains(3 ,$childs);
		$this->assertContains(4 ,$childs);
		$this->assertContains(5 ,$childs);
		$this->assertEquals(count($childs) , 4);
	}

	public function test_sortChilds()
	{
		$tree = new Tree();
		$tree->addItem('a', 0 , 50,1);
		$tree->addItem(1, 'a' , 100,1);
		$tree->addItem(2, 'a' , 200,2);
		$tree->addItem(3, 'a' , 300,3);
		$tree->setItemOrder(2, 4);
		$tree->sortItems('a');
		
		$items = $tree->getChilds('a');
		$newOrder = array();
		foreach ($items as $v)
			$newOrder[] = $v['data'];
			
		$this->assertEquals($newOrder,array(100,300,200));	
		
		
		$tree = new Tree();

		$tree->addItem(1, 0 , 100,1);
		$tree->addItem(2, 0 , 200,2);
		$tree->addItem(3, 0 , 300,3);
		$tree->setItemOrder(2, 4);
		$tree->sortItems();
		
		$items = $tree->getChilds(0);
		$newOrder = array();
		foreach ($items as $v)
			$newOrder[] = $v['data'];
			
		$this->assertEquals($newOrder,array(100,300,200));	
	}


	public function testGetChilds()
	{
		$tree = new Tree();
		$item = new stdClass();
		$tree->addItem(1, 0 , 'item1');
		$tree->addItem(2, 1 , 100);
		$tree->addItem(3, 1 , 200);
		$tree->addItem(4, 3 , 'item3-1');
		
		$childs = $tree->getChilds(1);
		$this->assertEquals(count($childs) , 2);
		$this->assertEquals($childs[2]['data'] , 100);
		$this->assertEquals($childs[3]['data'] , 200);
	}

	public function test_remove(){
		$tree = new Tree();
		$tree->addItem(1, 0 , 'item1');
		$tree->addItem(2, 1 , 'item2');
		$tree->addItem(3, 2 , 'item2');
		
		$tree->removeItem(2);
		$this->assertFalse($tree->itemExists(2));
		$this->assertFalse($tree->hasChilds(1));
		$this->assertFalse($tree->itemExists(3));
	}
	
	public function testGetParentId()
	{
		$tree = new Tree();
		$tree->addItem(1, 0 , 'item1');
		$tree->addItem(2, 1 , 'item2');
		
		$this->assertEquals($tree->getParentId(1),0);
		$this->assertEquals($tree->getParentId(2),1);
	}


	public function testChangeParent()
	{
		$tree = new Tree();
		$item = new stdClass();
		$tree->addItem(1, 0 , 'item1');
		$tree->addItem(2, 1 , 100);
		$tree->addItem(3, 1 , 200);
		$tree->addItem(4, 3 , 'item3-1');
		
		$tree->changeParent(4, 2);
		$this->assertEquals($tree->getParentId(4) , 2);
	}

	public function testRemoveItem()
	{
		$tree = new Tree();
		$item = new stdClass();
		$item->id = 1;
		$tree->addItem($item->id, 0 , $item);
		
		$item2 = new stdClass();
		$item2->id = 2;
		$tree->addItem($item2->id, 1 , $item2);
		
		$tree->removeItem(2);
		$this->assertEquals($tree->getItemsCount() , 1);
		$this->assertFalse($tree->itemExists(2));
		$this->assertFalse($tree->hasChilds(1));
	}

	public function testGetItems()
	{
		$tree = new Tree();
		$item = new stdClass();
		$item->id = 1;
		$tree->addItem($item->id, 0 , $item);
		
		$item2 = new stdClass();
		$item2->id = 2;
		$tree->addItem($item2->id, 1 , $item2);
		
		$data = $tree->getItems();
		$this->assertTrue(is_array($data));
		$this->assertEquals(count($data) , 2);
		$this->assertEquals($data[1]['data'] , $item);
		$this->assertEquals($data[2]['data'] , $item2);
	}
	
	public function testGetParentsList()
	{
	  $tree = new Tree();
	  $tree->addItem(1, 0 , 100);
	  $tree->addItem(2, 0 , 200);
	  $tree->addItem(3, 2 , 300);
	  $tree->addItem(4, 3 , 400);
	  $tree->addItem(5, 3 , 500);

	  $this->assertEquals($tree->getParentsList(5), array(2,3));
	}
}
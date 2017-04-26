<?php
use PHPUnit\Framework\TestCase;

class ExtTest extends TestCase
{
	public function testGetPropertyClass()
	{		
		$this->assertEquals(Ext::getPropertyClass('Grid'), 'Ext_Property_Grid');
		$this->assertEquals(Ext::getPropertyClass('Component_Field_System_Dictionary'), 'Ext_Property_Component_Field_System_Dictionary');
	}
}
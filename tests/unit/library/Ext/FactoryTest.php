<?php

class Ext_FactoryTest extends PHPUnit_Framework_TestCase
{
	public function testObject()
	{
		$this->assertTrue(Ext_Factory::object('Ext_Grid') instanceof Ext_Grid);
		$this->assertTrue(Ext_Factory::object('Ext_Component_Filter') instanceof Ext_Component_Filter);
		$this->assertTrue(Ext_Factory::object('Grid') instanceof Ext_Grid);
		$this->assertTrue(Ext_Factory::object('Docked') instanceof Ext_Docked);
		$this->assertTrue(Ext_Factory::object('Model') instanceof Ext_Model);
		$this->assertTrue(Ext_Factory::object('Panel') instanceof Ext_Virtual);
		$this->assertTrue(Ext_Factory::object('Store') instanceof Ext_Store);
		$this->assertTrue(Ext_Factory::object('Form') instanceof Ext_Virtual);
		$this->assertEquals(Ext_Factory::object('Form')->getClass() ,  'Form');
		$this->assertTrue(Ext_Factory::object('Tabpanel') instanceof Ext_Virtual);
		$this->assertEquals(Ext_Factory::object('Tabpanel')->getClass() ,  'Tabpanel');
		$this->assertTrue(Ext_Factory::object('Toolbar') instanceof Ext_Virtual);
		$this->assertEquals(Ext_Factory::object('Toolbar')->getClass() ,  'Toolbar');
		$this->assertTrue(Ext_Factory::object('Window') instanceof Ext_Virtual);
		$this->assertEquals(Ext_Factory::object('Window')->getClass() ,  'Window');
		$this->assertTrue(Ext_Factory::object('Grid_Column') instanceof Ext_Grid_Column);
		$this->assertTrue(Ext_Factory::object('Component_Field_System_Searchfield') instanceof Ext_Component_Field_System_Searchfield);
		$this->assertTrue(Ext_Factory::object('Component_Field_System_Medialibhtml') instanceof Ext_Component_Field_System_Medialibhtml);
		$this->assertTrue(Ext_Factory::object('Component_Field_System_Dictionary') instanceof Ext_Component_Field_System_Dictionary);
		$this->assertTrue(Ext_Factory::object('Component_Window_System_Crud') instanceof Ext_Virtual);
		$this->assertEquals(Ext_Factory::object('Component_Window_System_Crud')->getClass() ,  'Component_Window_System_Crud');
		$this->assertEquals(Ext_Factory::object('Component_Window_System_Crud_Vc')->getClass() ,  'Component_Window_System_Crud_Vc');
		
		$this->assertEquals(Ext_Factory::object('Form_Checkboxgroup')->getClass() ,  'Form_Checkboxgroup');
		$this->assertEquals(Ext_Factory::object('Form_Fieldcontainer')->getClass() ,  'Form_Fieldcontainer');
		$this->assertEquals(Ext_Factory::object('Form_Fieldset')->getClass() ,  'Form_Fieldset');
		$this->assertEquals(Ext_Factory::object('Form_Radiogroup')->getClass() ,  'Form_Radiogroup');
		$this->assertEquals(Ext_Factory::object('Form_Field_Checkbox')->getClass() ,  'Form_Field_Checkbox');
		
		$this->assertEquals(Ext_Factory::object('Form_Field_Combobox')->getClass() ,  'Form_Field_Combobox');
		$this->assertEquals(Ext_Factory::object('Form_Field_Date')->getClass() ,  'Form_Field_Date');
		$this->assertEquals(Ext_Factory::object('Form_Field_Display')->getClass() ,  'Form_Field_Display');
		$this->assertEquals(Ext_Factory::object('Form_Field_File')->getClass() ,  'Form_Field_File');
		$this->assertEquals(Ext_Factory::object('Form_Field_Htmleditor')->getClass() ,  'Form_Field_Htmleditor');
		$this->assertEquals(Ext_Factory::object('Form_Field_Number')->getClass() ,  'Form_Field_Number');
		$this->assertEquals(Ext_Factory::object('Form_Field_Text')->getClass() ,  'Form_Field_Text');
		$this->assertEquals(Ext_Factory::object('Form_Field_Textarea')->getClass() ,  'Form_Field_Textarea');
		$this->assertEquals(Ext_Factory::object('Form_Field_Time')->getClass() ,  'Form_Field_Time');
		$this->assertEquals(Ext_Factory::object('Form_Field_Radio')->getClass() ,  'Form_Field_Radio');
		
		$this->assertEquals(Ext_Factory::object('Data_Proxy_Ajax')->getClass() ,  'Data_Proxy_Ajax');

		$this->assertEquals(Ext_Factory::object('Data_Proxy_Direct')->getClass() ,  'Data_Proxy_Direct');
		$this->assertEquals(Ext_Factory::object('Data_Proxy_Jsonp')->getClass() ,  'Data_Proxy_Jsonp');
		$this->assertEquals(Ext_Factory::object('Data_Proxy_Localstorage')->getClass() ,  'Data_Proxy_Localstorage');
		$this->assertEquals(Ext_Factory::object('Data_Proxy_Memory')->getClass() ,  'Data_Proxy_Memory');
		$this->assertEquals(Ext_Factory::object('Data_Proxy_Rest')->getClass() ,  'Data_Proxy_Rest');
		$this->assertEquals(Ext_Factory::object('Data_Proxy_Sessionstorage')->getClass() ,  'Data_Proxy_Sessionstorage');

		

		$this->assertEquals(Ext_Factory::object('Data_Reader_Array')->getClass() ,  'Data_Reader_Array');
		$this->assertEquals(Ext_Factory::object('Data_Reader_Json')->getClass() ,  'Data_Reader_Json');
		$this->assertEquals(Ext_Factory::object('Data_Reader_Xml')->getClass() ,  'Data_Reader_Xml');
		
		$this->assertEquals(Ext_Factory::object('Data_Writer_Json')->getClass() ,  'Data_Writer_Json');
		$this->assertEquals(Ext_Factory::object('Data_Writer_Xml')->getClass() ,  'Data_Writer_Xml');
				
		$this->assertEquals(Ext_Factory::object('Grid_Column')->getClass() ,  'Grid_Column');
		$this->assertEquals(Ext_Factory::object('Grid_Column_Action')->getClass() ,  'Grid_Column_Action');
		$this->assertEquals(Ext_Factory::object('Grid_Column_Date')->getClass() ,  'Grid_Column_Date');
		$this->assertEquals(Ext_Factory::object('Grid_Column_Boolean')->getClass() ,  'Grid_Column_Boolean');
		$this->assertEquals(Ext_Factory::object('Grid_Column_Number')->getClass() ,  'Grid_Column_Number');
		$this->assertEquals(Ext_Factory::object('Grid_Column_Template')->getClass() ,  'Grid_Column_Template');
		
		
		$this->assertEquals(Ext_Factory::object('Toolbar_Fill')->getClass() ,  'Toolbar_Fill');
		$this->assertEquals(Ext_Factory::object('Toolbar_Item')->getClass() ,  'Toolbar_Item');
		$this->assertEquals(Ext_Factory::object('Toolbar_Paging')->getClass() ,  'Toolbar_Paging');
		$this->assertEquals(Ext_Factory::object('Toolbar_Separator')->getClass() ,  'Toolbar_Separator');
		$this->assertEquals(Ext_Factory::object('Toolbar_Spacer')->getClass() ,  'Toolbar_Spacer');
		$this->assertEquals(Ext_Factory::object('Toolbar_Textitem')->getClass() ,  'Toolbar_Textitem');
		
		$this->assertEquals(Ext_Factory::object('Button')->getClass() ,  'Button');
		
		$o2 = Ext_Factory::object('Grid' , array('width'=>100,'height'=>20));
		$this->assertEquals($o2->width, 100);
		$this->assertEquals($o2->height, 20);
	}
	
	public function testCopyProperties()
	{
		$o1 = Ext_Factory::object('Grid' , array('width'=>100,'height'=>20));
		$o2 = Ext_Factory::object('Grid');

		$this->assertFalse($o1->getConfig()->__toArray() === $o2->getConfig()->__toArray());
		
		Ext_Factory::copyProperties($o1, $o2);
		$this->assertEquals($o1->getConfig()->__toArray(), $o2->getConfig()->__toArray());
	}
	
	
	public function testGetEventsGrid()
	{
		$this->assertTrue(Ext_Factory::getEvents('Ext_Grid') instanceof  Ext_Events_Grid);
		$this->assertTrue(Ext_Factory::getEvents('Grid') instanceof Ext_Events_Grid);
		$this->assertTrue(Ext_Factory::getEvents('Grid_Column') instanceof  Ext_Events_Grid_Column);
		$this->assertTrue(Ext_Factory::getEvents('Grid_Column_Action') instanceof  Ext_Events_Grid_Column_Action);
		$this->assertTrue(Ext_Factory::getEvents('Grid_Column_Date') instanceof  Ext_Events_Grid_Column_Date);
		$this->assertTrue(Ext_Factory::getEvents('Grid_Column_Boolean') instanceof  Ext_Events_Grid_Column_Boolean);
		$this->assertTrue(Ext_Factory::getEvents('Grid_Column_Number') instanceof  Ext_Events_Grid_Column_Number);
		$this->assertTrue(Ext_Factory::getEvents('Grid_Column_Template') instanceof  Ext_Events_Grid_Column_Template);		
	}
	
	public function testGetEventsComponents()
	{
		//$this->assertTrue(Ext_Factory::getEvents('Ext_Component_Filter') instanceof Ext_Events_Component_Filter);
		//$this->assertTrue(Ext_Factory::getEvents('Component_Field_System_Searchfield') instanceof Ext_Events_Component_Field_System_Searchfield);
		//$this->assertTrue(Ext_Factory::getEvents('Component_Field_System_Medialibhtml') instanceof Ext_Events_Component_Field_System_Medialibhtml);
		//$this->assertTrue(Ext_Factory::getEvents('Component_Field_System_Dictionary') instanceof Ext_Events_Component_Field_System_Dictionary);
		$this->assertTrue(Ext_Factory::getEvents('Component_Window_System_Crud') instanceof Ext_Events_Component_Window_System_Crud);
		$this->assertTrue(Ext_Factory::getEvents('Component_Window_System_Crud_Vc') instanceof Ext_Events_Component_Window_System_Crud_Vc);
		
	}
	
	public function testGetEventsForm()
	{		
		$this->assertTrue(Ext_Factory::getEvents('Form') instanceof Ext_Events_Form);
		$this->assertTrue(Ext_Factory::getEvents('Form_Checkboxgroup') instanceof  Ext_Events_Form_Checkboxgroup);
		$this->assertTrue(Ext_Factory::getEvents('Form_Fieldcontainer') instanceof  Ext_Events_Form_Fieldcontainer);
		$this->assertTrue(Ext_Factory::getEvents('Form_Fieldset') instanceof  Ext_Events_Form_Fieldset);
		$this->assertTrue(Ext_Factory::getEvents('Form_Radiogroup') instanceof  Ext_Events_Form_Radiogroup);
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_Checkbox') instanceof  Ext_Events_Form_Field_Checkbox);	
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_Combobox') instanceof  Ext_Events_Form_Field_Combobox);
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_Date') instanceof  Ext_Events_Form_Field_Date);
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_Display') instanceof  Ext_Events_Form_Field_Display);
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_File') instanceof  Ext_Events_Form_Field_File);
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_Htmleditor') instanceof  Ext_Events_Form_Field_Htmleditor);
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_Number') instanceof  Ext_Events_Form_Field_Number);
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_Text') instanceof  Ext_Events_Form_Field_Text);
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_Textarea') instanceof  Ext_Events_Form_Field_Textarea);
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_Time') instanceof  Ext_Events_Form_Field_Time);
		$this->assertTrue(Ext_Factory::getEvents('Form_Field_Radio') instanceof  Ext_Events_Form_Field_Radio);
		
	}
	
	public function testGetEventsToolbar()
	{			
		$this->assertTrue(Ext_Factory::getEvents('Toolbar') instanceof Ext_Events_Toolbar);
		$this->assertTrue(Ext_Factory::getEvents('Toolbar_Fill') instanceof  Ext_Events_Toolbar_Fill);
		$this->assertTrue(Ext_Factory::getEvents('Toolbar_Item') instanceof  Ext_Events_Toolbar_Item);
		$this->assertTrue(Ext_Factory::getEvents('Toolbar_Paging') instanceof  Ext_Events_Toolbar_Paging);
		$this->assertTrue(Ext_Factory::getEvents('Toolbar_Separator') instanceof  Ext_Events_Toolbar_Separator);
		$this->assertTrue(Ext_Factory::getEvents('Toolbar_Spacer') instanceof  Ext_Events_Toolbar_Spacer);
		$this->assertTrue(Ext_Factory::getEvents('Toolbar_Textitem') instanceof  Ext_Events_Toolbar_Textitem);		
	}
	
	public function testGetEventsOther()
	{	
		$this->assertTrue(Ext_Factory::getEvents('Store') instanceof Ext_Events_Store);
		$this->assertTrue(Ext_Factory::getEvents('Tabpanel') instanceof Ext_Events_Tabpanel);	
		$this->assertTrue(Ext_Factory::getEvents('Window') instanceof Ext_Events_Window);
		$this->assertTrue(Ext_Factory::getEvents('Button') instanceof  Ext_Events_Button);
		$this->assertTrue(Ext_Factory::getEvents('Grid_Column_Action_Button') instanceof  Ext_Events_Grid_Column_Action_Button);
	}
}
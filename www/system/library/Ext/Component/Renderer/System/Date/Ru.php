<?php
class Ext_Component_Renderer_System_Date_Ru extends Ext_Component_Renderer
{
	public function __toString()
	{
		return 'Ext.util.Format.dateRenderer("d.m.Y")';
	}
}

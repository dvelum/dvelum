<?php
/**
 * File adapter for Query_Storage
 * @author Kirill A Egorov 2011
 */
class Db_Query_Storage_Adapter_File extends Db_Query_Storage_Adapter_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see Db_Query_Storage_Adapter_Abstract::load()
	 */
	public function load($id)
	{
		if(! is_file($id))
			throw new Exception('Invalid file path');
		return $this->_unpack(file_get_contents($id));
	}

	/**
	 * (non-PHPdoc)
	 * @see Db_Query_Storage_Adapter_Abstract::save()
	 */
	public function save($id , Db_Query $obj)
	{
		return @file_put_contents($id , $this->_pack($obj));
	}

	/**
	 * (non-PHPdoc)
	 * @see Db_Query_Storage_Adapter_Abstract::delete()
	 */
	public function delete($id)
	{
		return unlink($id);
	}
}
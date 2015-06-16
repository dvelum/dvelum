<?php
/**
 * Abstract Base for Db_Query_Storage 
 * @author Kirill A Egorov 2011
 */
abstract class Db_Query_Storage_Adapter_Abstract
{
	/**
	 * Load Db_Query object
	 * @param string $id
	 * @throws Exception
	 * @return Db_Query
	 */
	abstract public function load($id);

	/**
	 * Save Db_Query object
	 * @param string $id
	 * @param Db_Query $obj
	 * @return boolean
	 */
	abstract public function save($id , Db_Query $obj);

	/**
	 * Delete Db_Query object
	 * @param string $id
	 * @return boolean
	 */
	abstract public function delete($id);

	/**
	 * Pack object
	 * @param Db_Query $query
	 * @return string
	 */
	protected function _pack(Db_Query $query)
	{
		return base64_encode(serialize($query));
	}

	/**
	 * Unpack object
	 * @param string $data
	 * @throws Exception
	 * @return Db_Query
	 */
	protected function _unpack($data)
	{
		$query = unserialize(base64_decode($data));
		
		if(! $query instanceof Db_Query)
			throw new Exception('Invalid data type');
		
		return $query;
	}
}
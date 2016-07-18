<?php
/**
 * Db_Object information expert
 * Helps to find  relations between objects
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2011-2012  Kirill A Egorov,
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @license General Public License version 3
 *
 * @todo Test (its betta version)
 */
class Db_Object_Expert
{
	static protected $_objectAssociations = null;

	static protected function _buildAssociations()
	{
		if(!is_null(self::$_objectAssociations))
			return;

		$manager = new Db_Object_Manager();
		$objects = $manager->getRegisteredObjects();
		foreach ($objects as $name){
			$config = Db_Object_Config::getInstance($name);
			$links = $config->getLinks();
			self::$_objectAssociations[$name] = $links;
		}
	}
	/**
	 * Get Associated objects
	 * @param Db_Object $object
	 * @return array   like
	 * array(
	 * 	  'single' => array(
	 * 			'objectName'=>array(id1,id2,id3),
	 * 			...
	 * 			'objectNameN'=>array(id1,id2,id3),
	 * 	   ),
	 * 	   'multi' =>array(
	 * 			'objectName'=>array(id1,id2,id3),
	 * 			...
	 * 			'objectNameN'=>array(id1,id2,id3),
	 * 	   )
	 * )
	 */
	static public function getAssociatedObjects(Db_Object $object)
	{
		$linkedObjects = array('single'=>array(),'multi'=>array());

		self::_buildAssociations();

		$objectName = $object->getName();
		$objectId = $object->getId();

		if(!isset(self::$_objectAssociations[$objectName]))
			return array();

		foreach (self::$_objectAssociations as $testObject=>$links)
		{
			if(!isset($links[$objectName]))
				continue;

			$sLinks = self::_getSingleLinks($objectId , $testObject , $links[$objectName]);

			 if(!empty($sLinks))
			 	$linkedObjects['single'][$testObject] = $sLinks;
		}

		$linkedObjects['multi'] = self::_getMultiLinks($objectName, $objectId);

		return $linkedObjects;
	}
	/**
	 * Get "single link" associations
	 * when object has link as own property
	 * @param integer $objectId
	 * @param string $relatedObject - related object name
	 * @param array $links - links config like
	 * 	array(
	 * 		'field1'=>'object',
	 * 		'field2'=>'multi'
	 * 		...
	 * 		'fieldN'=>'object',
	 *  )
	 *  @return array
	 */
	static protected function _getSingleLinks($objectId, $relatedObject , $links)
	{
		$relatedConfig = Db_Object_Config::getInstance($relatedObject);
		$relatedObjectModel = Model::factory($relatedObject);
		$fields = array();
		$singleRelated = array();
		foreach($links as $field=>$type)
		{
			if($type!=='object')
				continue;

			$fields[] = $field;
		}

		if(empty($fields))
			return  array();

			$db = $relatedObjectModel->getDbConnection();
			$sql = $db->select()->from($relatedObjectModel->table() , array($relatedConfig->getPrimaryKey()));
			$first = true;
			foreach ($fields as $field){
				if($first){
					$sql->where($db->quoteIdentifier($field).' =?' , $objectId);
				}else{
					$sql->orWhere($db->quoteIdentifier($field).' =?' , $objectId);
					$first = false;
				}
			}
			$data = $db->fetchAll($sql);


			if(empty($data))
				return array();

			return Utils::fetchCol($relatedConfig->getPrimaryKey(), $data);
	}
	/**
	 * Get multi-link associations
	 * when links stored  in external objects
	 * @param string $objectName
	 * @param integer $objectId
	 * @return array
	 */
	static protected function _getMultiLinks($objectName , $objectId)
	{
		$configMain = Registry::get('main', 'config');

		$linksModel = Model::factory($configMain->get('orm_links_object'));
		$db = $linksModel->getDbConnection();
		$linkTable = $linksModel->table();

		$sql = $db->select()
				  ->from($linkTable, array('id'=>'src_id','object'=>'src'))
				  ->where('`target` =?',$objectName)
				  ->where('`target_id` =?', $objectId);
		$links = $db->fetchAll($sql);

		$data = array();

		if(!empty($links))
			foreach ($links as $record)
				$data[$record['object']][] = $record['id'];

		return $data;
	}
	/**
	 * Check if Object has associated objects
	 * @param string $objectName
	 * @return array - associations
	 */
	static public function getAssociatedStructures($objectName)
	{
		$objectName = strtolower($objectName);

		self::_buildAssociations();

		if(empty(self::$_objectAssociations))
			return array();

		$associations = array();

		foreach (self::$_objectAssociations as $object=>$data)
		{
			if(empty($data))
				continue;

			foreach ($data as $oName => $fields)
			{
				if($oName !== $objectName)
					continue;

				$associations[] =  array(
					'object'=>$object,
					'fields'=> $fields
				);
			}
		}
		return $associations;
	}
}
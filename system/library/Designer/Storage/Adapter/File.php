<?php
/*
* DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
* Copyright (C) 2011-2013  Kirill A Egorov
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * File adapter for Designer_Storage
 * @author Kirill A Egorov 2012
 */
class Designer_Storage_Adapter_File extends Designer_Storage_Adapter_Abstract
{
	protected $_configPath = '';
	protected $dirPostfix = '.files';

	protected $exportPath;

	public function getContentDir($projectFilePath)
	{
		return $projectFilePath . $this->dirPostfix . '/';
	}

	/**
	 * @param array $config, optional
	 */
	public function __construct($config = false)
	{
		parent::__construct($config);
		if($config)
			$this->setConfigsPath($config->get('configs'));
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Designer_Storage_Adapter_Abstract::load()
	 */
	public function load($id)
	{
		if(!is_file($id))
			throw new Exception('Invalid file path' . $id );
		return $this->_unpack(file_get_contents($id));
	}

	/**
	 * (non-PHPdoc)
	 * @see Designer_Storage_Adapter_Abstract::save()
	 */
	public function save($id , Designer_Project $obj)
	{
		$result = @file_put_contents($id , $this->_pack($obj));

		if($result == false){
			$this->_errors[] = 'write: ' . $id;
			return false;
		}
		return $this->export($id , $obj);
	}

	/**
	 * (non-PHPdoc)
	 * @see Designer_Storage_Adapter_Abstract::delete()
	 */
	public function delete($id)
	{
		$id = $this->_configPath . $id;
		return unlink($id);
	}
	
	/**
	 * Set path to configs directory
	 * @param string $path
	 */
	public function setConfigsPath($path)
	{
		$this->_configPath = $path;
	}

	/**
	 * Export project data for VCS
	 * @param $file - project file path
	 * @param Designer_Project $project
	 */
	protected function export($file , Designer_Project $project)
	{
		$this->_errors = array();

		$this->exportPath = $this->getContentDir($file);

		if(!is_dir($this->exportPath)) {
			if(!@mkdir($this->exportPath , 0775)){
				return false;
			}
		}else{
			File::rmdirRecursive($this->exportPath);
		}

		/*
		$dump = array(
			'file' => $file,
			'checksum' => md5_file($file),
			'date' => date('Y-m-d H:i:s'),
			'dump' => $project
		);

		if(!Utils::exportArray($projectPath.'dump.php' , $dump)) {
			$this->_errors[] = 'write: '.$projectPath.'config.php';
			return false;
		}
		*/

		// Export Project config
		if(!Utils::exportArray($this->exportPath.'__config.php' , $project->getConfig())){
			$this->_errors[] = 'write: ' . $this->exportPath . '__config.php';
			return false;
		}
		// Export Project events
		$events = $this->exportEvents($project);

		if($events === false)
			return false;

		if(!Utils::exportArray($this->exportPath.'__events.php' , $events)){
			$this->_errors[] = 'write: ' . $this->exportPath . '__events.php';
			return false;
		}
		// Export Project methods
		$methods = $this->exportMethods($project);

		if($methods === false)
			return false;

		if(!Utils::exportArray($this->exportPath.'__methods.php' , $methods)){
			$this->_errors[] = 'write: ' . $this->exportPath . '__methods.php';
			return false;
		}

		// Export Project Tree
		$tree = $this->parseTree($project);
		if($tree === false){
			return false;
		}

		if(!Utils::exportArray($this->exportPath . '__tree.php' , $tree)){
			$this->_errors[] = 'write: ' . $this->exportPath . '__tree.php';
			return false;
		}
		return true;
	}

	/**
	 * Create project items array
	 * @param Designer_Project $project
	 */
	protected function parseTree(Designer_Project $project)
	{
		$result = array();
		$items = $project->getTree()->getItems();
		$items = Utils::sortByField($items , 'parent');
		foreach($items as $k=>$v)
		{
			$exportedObject = $this->exportObject($v['id'] , $v['data']);

			if($exportedObject === false){
				return false;
			}

			$v['data'] = $exportedObject;
			$result[$v['id']] = $v;

		}unset($v);

		return $result;
	}
	/**
	 *
	 */
	protected function exportObject($id , $object)
	{
		$objectFile = $this->exportPath . $id . '.config.php';

		$config = array(
			'id' => $id,
			'class' => get_class($object),
			//'state_dump' => $object
		);

		if($object instanceof Ext_Object)
		{
			$config['extClass']= $object->getClass();
			$config['name'] = $object->getName();
			$config['state'] = $object->getState();
		}

		if(!Utils::exportArray($objectFile , $config)){
			$this->_errors[] = 'write: ' . $objectFile;
			return false;
		}

		return  $id . '.config.php';
	}

	/**
	 * Export project events
	 */
	protected function exportEvents(Designer_Project $project)
	{
		$eventManager = $project->getEventManager();
		$list = $eventManager->getEvents();
		$eventsIndex = array();

		foreach($list as $object => $events)
		{
			if(empty($events)){
				continue;
			}

			foreach($events as $name => &$data)
			{
				if(!empty($data['code']))
				{
					$eventFile = $this->exportPath . $object . '.events.' . $name . '.js';
					if(!@file_put_contents($eventFile , $data['code'])){
						$this->_errors[] = 'write: ' . $eventFile;
						return false;
					}
					$data['code'] = $object . '.events.' . $name . '.js';
				}else{
					$data['code'] = false;
				}
			}
			$listFile = $this->exportPath . $object . '.events.php';
			if(!Utils::exportArray($listFile , $events)){
				$this->_errors[] = 'write: ' . $eventFile;
				return false;
			}
			$eventsIndex[$object] = $object . '.events.php';
		}
		return $eventsIndex;
	}

	/**
	 * Export project events
	 */
	protected function exportMethods(Designer_Project $project)
	{
		$methodManager = $project->getMethodManager();
		$list = $methodManager->getMethods();
		$methodIndex = array();

		foreach($list as $object => $methods)
		{
			if(empty($methods)){
				continue;
			}

			foreach($methods as $name => $item)
			{
				if(!$item instanceof Designer_Project_Methods_Item){
					continue;
				}

				$data = array(
					'name' => $item->getName(),
					'code' => $item->getCode(),
					'description' => $item->getDescription(),
					'params' => $item->getParams(),
				);

				if(!empty($data['code']))
				{
					$eventFile = $this->exportPath . $object . '.methods.' . $name . '.js';
					if(!@file_put_contents($eventFile , $data['code'])){
						$this->_errors[] = 'write: ' . $eventFile;
						return false;
					}
					$data['code'] = $object . '.methods.' . $name . '.js';
				}else{
					$data['code'] = false;
				}
			}
			$listFile = $this->exportPath . $object . '.methods.php';
			if(!Utils::exportArray($listFile , $data)){
				$this->_errors[] = 'write: ' . $eventFile;
				return false;
			}
			$methodIndex[$object] = $object . '.methods.php';
		}
		return $methodIndex;
	}

	/**
	 * Import project from content dir
	 * @param string $file
	 * @return Designer_Project | false
	 */
	public function import($file)
	{
		$this->exportPath = $this->getContentDir($file);
		$baseFiles = array('__config.php','__tree.php','__events.php','__methods.php');

		foreach($baseFiles as $file){
			if(file_exists($this->exportPath . $file))
				return false;
		}

		$config = require $this->exportPath . '__config.php';
		$project = new Designer_Project();
		$project->setConfig($config);

		$treeData = require $this->exportPath . '__tree.php';
		$this->importTree($project , $treeData);


		return $project;
	}

	/**
	 * Restore project Tree from config
	 * @param Designer_Project $project
	 * @param $data
	 */
	protected function importTree(Designer_Project $project , $data)
	{
		$tree = $project->getTree();
		foreach($data as $id=>$v)
		{
			$cfg = require $this->_configPath . $v['data'];

			if($v['class'] == 'Designer_Project_Container'){
				$o = new Designer_Project_Container($id);
			}else{
				//$o = new $v['class']($v['name']);
				$o = Ext_Factory::object($v['extClass']);
				$o->setState($v['state']);
			}
			$tree->addItem($v['id'],$v['parent'], $o, $v['order']);
		}
	}
}
<?php
class Model_Filestorage extends Model
{
	/**
	 * Get file storage
	 * @return Filestorage_Abstract
	 */
	public function getStorage()
	{
		$configMain = Registry::get('main' , 'config');

		$storageConfig = Config::storage()->get('filestorage.php');
		$storageCfg = new Config_Simple('_filestorage');

		if($configMain->get('development')){
			$storageCfg->setData($storageConfig->get('development'));
		}else{
			$storageCfg->setData($storageConfig->get('production'));
		}

		$storageCfg->set('user_id', User::getInstance()->id);

		$fileStorage = Filestorage::factory($storageCfg->get('adapter'), $storageCfg);

		$log = $this->getLogsAdapter();

		if($log)
			$fileStorage->setLog($log);

		return $fileStorage;
	}
}
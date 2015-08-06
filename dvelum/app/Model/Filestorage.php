<?php
class Model_Filestorage extends Model
{
	/**
	 * Инициализировать файлохранилище
	 * @return Filestorage_Abstract
	 */
	public function getStorage()
	{
		$configMain = Registry::get('main' , 'config');

		$storageConfig = Config::factory(Config::File_Array, $configMain->get('configs').'/filestorage.php');
		$storageCfg = new Config_Simple($configMain->get('configs').'_filestorage');

		if($configMain->get('development')){
			$storageCfg->setData($storageConfig->get('development'));
		}else{
			$storageCfg->setData($storageConfig->get('production'));
		}

		$storageCfg->set('user_id', User::getInstance()->id);

		$fileStorage = Filestorage::factory($storageCfg->get('adapter'), $storageCfg);
		$fileStorage->setLog($this->getLogsAdapter());

		return $fileStorage;
	}
}
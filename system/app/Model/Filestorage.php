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
		$storageConfig->set('user_id', User::getInstance()->id);

		$fileStorage = Filestorage::factory($storageConfig->get('adapter'), $storageConfig);
		$fileStorage->setLog($this->getLogsAdapter());

		return $fileStorage;
	}
}
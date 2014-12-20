<?php
/**
 * Background task
 * Test task
 * @author Kirill Egorov
 */
class Task_Deploy_Archive extends Bgtask_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see Bgtask_Abstract::getDescription()
	 */
	public function getDescription(){
		return Lang::lang()->CREATE_DEPLOY_PACKAGE;
	}
	/**
	 * (non-PHPdoc)
	 * @see Bgtask_Abstract::run()
	 */
	public function run()
	{
		$lang = Lang::lang();
		
		$appConfig = Registry::get('main' , 'config');
		$docRoot = $appConfig->get('docroot') . DIRECTORY_SEPARATOR;
		$deployCfg = Config::factory(Config::File_Array, $appConfig->get('configs').'deploy.php');
	
		$this->setTotalCount(3);

		$dirName = $deployCfg->get('datadir') . $this->_config['server'] . DIRECTORY_SEPARATOR . date('Y-m-d_H_i_s');
		
		if(!is_dir($dirName) && !mkdir($dirName , 0775 , true))
			$this->error($lang->CANT_WRITE_FS . '('.$dirName.')');
		
		$dirName.= DIRECTORY_SEPARATOR;	
		$wwwBackupDir = $dirName .'www/';
			
		$this->_nextStep();
		
		if(isset($this->_config['files']) && !empty($this->_config['files']) && is_array($this->_config['files']))
		{		
			if(!File::zipFiles($dirName . 'www.zip',  $this->_config['files'], $dirName))
				$this->error($lang->CANT_WRITE_FS . '(' . $dirName . 'www.zip' . ')');
						
		}
		
		$this->_nextStep();
		
		if(isset($this->_config['files_delete']) && !empty($this->_config['files_delete']) && is_array($this->_config['files_delete']))
		{
			if(!@file_put_contents($dirName . 'delete.txt', implode("\n", $this->_config['files_delete'])))
				$this->error($lang->CANT_WRITE_FS . '(' . $dirName . 'delete.txt' . ')');
			
			if(!File::zipFiles($dirName . 'www.zip',  array($dirName . 'delete.txt'), $dirName)){
				$this->error($lang->CANT_WRITE_FS . '(' . $dirName . 'www.zip' . ')');
			}
			
			unlink($dirName . 'delete.txt');
		}
		
		$this->_nextStep();
		$this->finish();	
	}
	
	protected function _nextStep($count = 1){
		$this->incrementCompleted($count);
        $this->updateState();
        $this->processSignals();
	}
}
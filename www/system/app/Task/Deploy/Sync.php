<?php
/**
 * Background task
 * Test task
 * @author Kirill Egorov
 */
class Task_Deploy_Sync extends Bgtask_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see Bgtask_Abstract::getDescription()
	 */
	public function getDescription(){
		return  Lang::lang()->DEPLOY_SYNC_REQUEST .' '. $this->_config['name'];
	}
	/**
	 * (non-PHPdoc)
	 * @see Bgtask_Abstract::run()
	 */
	public function run()
	{	
		$lang =  Lang::lang();
		$this->setTotalCount(4);
		$config = Registry::get('main' , 'config');
		$deployCfg = Config::factory(Config::File_Array, $config->get('configs') .'deploy.php');
		
		$delimiter = $config->get('urlDelimetr');

		$url = 'http://' . str_replace(
				array('http://' , $delimiter.$delimiter , $config->get('urlExtension')),
				array('' , $delimiter , ''),
				$this->_config['url'] . $delimiter . 'deploy' .$delimiter. 'syncfiles'
		);
		
		$dataSend = array(
				'key'=>Utils::hash($this->_config['key'])
		);

		$curl = curl_init();

		$this->log('Connecting to ' .  $url . '...');
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_VERBOSE, 0);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $dataSend); 
		curl_setopt($curl, CURLOPT_TIMEOUT , 3600);
	
		$result = curl_exec($curl);
	
		$this->_nextStep();
		
		if($result === false)
			$this->error(curl_error($curl));
		
		$data = json_decode($result , true);
		
		if(empty($data) || !is_array($data))
			$this->error('Empty response from server');
		
		
		if(!$data['success'])
			$this->error('Remote server error. ' . $data['msg']);
		
		if(!isset($data['data']['files']))
			$this->error('Invalid result');
		
		$data = $data['data'];
				
		$serverDir = $deployCfg->get('datadir').$this->_config['id'].'/';
				
		if(!file_exists($serverDir) && !mkdir($serverDir)) 
			$this->error($lang->CANT_WRITE_FS . '('.$serverDir.')');
		$this->_nextStep();	
					
		if(!Utils::exportArray($serverDir . 'map.php', $data['files'])) 
			$this->error($lang->CANT_WRITE_FS. '('.$serverDir . 'map.php'.')');
		$this->_nextStep();	
					
		if(!@file_put_contents($serverDir . 'lastfsupdate' , date('Y-m-d H:i:s'))) 
			$this->error($lang->CANT_WRITE_FS. '('.$serverDir . 'lastfsupdate'.')');
		$this->_nextStep();

        $this->finish();
	}
	
	protected function _nextStep(){
		$this->incrementCompleted();
        $this->updateState();
        $this->processSignals();
	}
}
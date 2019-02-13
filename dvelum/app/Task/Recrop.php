<?php
/**
 * Background task
 * Recrop medialibrary images
 * @author Kirill Egorov
 * 
 * Requires config  
 * 	types - array of image typecodes like array('icon','thumb',...)
 *  notCroped - boolean flag  - crop only autocroped images
 */
use Dvelum\Config;

class Task_Recrop extends Bgtask_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see Bgtask_Abstract::getDescription()
	 */
	public function getDescription(){
		$lang =  Lang::lang();
		Return $lang->TASK_MEDIALIB_RECROP . ': ' . implode(',', $this->_config['types']);
	}
	/**
	 * (non-PHPdoc)
	 * @see Bgtask_Abstract::run()
	 */
	public function run()
	{
		$mediaModel = Model::factory('Medialib');
		$types = $this->_config['types'];
		$nonCroped = $this->_config['notCroped'];
		
		$wwwPath = Config::storage()->get('main.php')->get('wwwPath');
		
		$filter = array('type'=>'image');
		
		if($nonCroped)
			$filter['croped'] = 0;
		
		$data = $mediaModel->getListVc(false ,$filter,false,array('path' , 'ext' , 'croped'));
		
		if(empty($data))
			$this->finish();

        $conf = $mediaModel->getConfig()->__toArray();
        
        $thumbSizes = $conf['image']['sizes'];

        if(!$types || !is_array($types))
        	return;
        		
        $this->setTotalCount(sizeof($data));
        	
         foreach ($data as $v)
         {
             // sub dir fix
             if($v['path'][0]!=='/')
                 $v['path'] = '/' . $v['path'];
             
            $path = $wwwPath . $v['path'];

            if(!file_exists($path)){
            	$this->log('Skip  non existent file: ' . $path);
                continue;
            }
      
            foreach ($types as $typename)
            { 
	            if(!isset($thumbSizes[$typename]))
	                continue;
	                       		
	            $saveName = str_replace($v['ext'], '-'.$typename.$v['ext'], $path);


                switch($conf['image']['thumb_types'][$typename]){
                    case 'crop' :
                        Image_Resize::resize($path, $thumbSizes[$typename][0], $thumbSizes[$typename][1], $saveName, true,true);
                        break;
                    case 'resize_fit':
                        Image_Resize::resize($path, $thumbSizes[$typename][0], $thumbSizes[$typename][1], $saveName, true, false);
                        break;
                    case 'resize':
                        Image_Resize::resize($path, $thumbSizes[$typename][0], $thumbSizes[$typename][1], $saveName, false ,false);
                        break;
                    case 'resize_to_frame':
                        Image_Resize::resizeToFrame($path, $thumbSizes[$typename][0], $thumbSizes[$typename][1], $saveName);
                        break;
                }
            }                  
            /*
             * Update task status and check for signals 
             */
            $this->incrementCompleted();
            $this->updateState();
            $this->processSignals();
        }
        $this->finish();
	}
}
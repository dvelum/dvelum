<?php

/**
 * Medialibrary configuration module controller
 * Backoffice UI
 */
class Backend_Mediaconfig_Controller extends Backend_Controller
{
    public function indexAction()
    {
        $this->_resource->addJs('/js/app/system/Mediaconfig.js'  , 4);
        $this->_resource->addJs('/js/app/system/crud/mediaconfig.js'  , 5);

        $this->_resource->addInlineJs('
        	var canEdit = '.((boolean)$this->_user->canEdit($this->_module)).';
        	var canDelete = '.((boolean)$this->_user->canDelete($this->_module)).';
        ');
    }
    /**
     * Get configuration list
     */
    public function listAction()
    {
        $media = Model::factory('Medialib');
        $config = $media->getConfig()->__toArray();

        $result = array();

        foreach ($config['image']['sizes'] as $code => $item){
            $resize = 'crop';
            if(isset($config['image']['thumb_types'][$code]))
                $resize = $config['image']['thumb_types'][$code];

            $result[] = array(
                'code'=>$code,
                'resize'=>$resize,
                'width'=>$item[0],
                'height'=>$item[1]
            );
        }
        Response::jsonSuccess($result);
    }
    /**
     * Update configuration
     */
    public function updateAction()
    {
        $this->_checkCanEdit();

        $data = Request::post('data', 'raw', false);

        if($data === false)
            Response::jsonSuccess();

        $dataType = json_decode($data);
        if(!is_array($dataType)){
            $data = array(json_decode($data , true));
        }else{
            $data = json_decode($data , true);
        }

        $media = Model::factory('Medialib');
        $configImage = $media->getConfig()->get('image');

        foreach ($data as $item){
            $code = Filter::filterValue('pagecode', $item['code']);
            $configImage['sizes'][$code] = array(intval($item['width']) , intval($item['height']));
            $configImage['thumb_types'][$code] = $item['resize'];
        }

        $config = $media->getConfig();
        $config->set('image', $configImage);

        if(!$config->save())
            Response::jsonError($this->_lang->CANT_WRITE_FS);

        Response::jsonSuccess();
    }
    /**
     * Remove configuration record
     */
    public function deleteAction()
    {
        $this->_checkCanDelete();

        $data = Request::post('data', 'raw',false);

        if($data === false)
            Response::jsonSuccess();


        $dataType = json_decode($data);
        if(!is_array($dataType)){
            $data = array(json_decode($data , true));
        }else{
            $data = json_decode($data , true);
        }

        $media = Model::factory('Medialib');
        $configImage = $media->getConfig()->get('image');

        foreach ($data as $item){
            $code = Filter::filterValue('pagecode', $item['code']);
            unset($configImage['sizes'][$code]);
            unset($configImage['thumb_types'][$code]);
        }

        $config = $media->getConfig();
        $config->set('image', $configImage);

        if(!$config->save())
            Response::jsonError($this->_lang->CANT_WRITE_FS);

        Response::jsonSuccess();
    }
    /**
     * Recrop
     */
    public function startCropAction()
    {
        $this->_checkCanEdit();

        $notCroped = Request::post('notcroped', 'boolean', false);
        $sizes = Request::post('size', 'array', array());

        if(empty($sizes) || !is_array($sizes))
            Response::jsonError($this->_lang->MSG_SELECT_SIZE);

        $mediaConfig = Model::factory('Medialib')->getConfig()->__toArray();
        $acceptedSizes = array_keys($mediaConfig['image']['sizes']);
        $sizeToCrop = array();

        foreach ($sizes as $key=>$item)
            if(in_array($item, $acceptedSizes,true))
                $sizeToCrop[] = $item;

        if(empty($sizeToCrop))
            Response::jsonError($this->_lang->MSG_SELECT_SIZE);

        Model::factory('bgtask')->getDbConnection()->getProfiler()->setEnabled(false);

        $bgStorage = new Bgtask_Storage_Orm(Model::factory('bgtask') , Model::factory('Bgtask_Signal'));
        $logger = new Bgtask_Log_File($this->_configMain['task_log_path'] . 'recrop_' . date('d_m_Y__H_i_s'));
        $tm = Bgtask_Manager::getInstance();
        $tm->setStorage($bgStorage);
        $tm->setLogger($logger);
        $tm->launch(Bgtask_Manager::LAUNCHER_JSON, 'Task_Recrop' , array('types'=>$sizeToCrop,'notCroped'=>$notCroped));
    }
    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] =  '/js/app/system/Mediaconfig.js';
        /*
         * Module bootstrap
         */
        if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}

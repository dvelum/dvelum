<?php
class Backend_Cache_Controller extends Backend_Controller_Crud
{
    public function indexAction()
    {
        parent::indexAction();

        $this->_resource->addJs('/js/app/system/Cache.js' , true , 1);

        $this->_resource->addInlineJs('
	        	var canEdit = '.($this->_user->canEdit($this->_module)).';
	        	var canDelete = '.($this->_user->canDelete($this->_module)).';
	    ');
    }

    /**
     * Get cache state info
     */
    public function infoAction()
    {
        $cacheManager = new Cache_Manager();

        $sysCache = $cacheManager->get('system');
        $dataCache = $cacheManager->get('data');
        $results = array();

        if($sysCache && $sysCache instanceof Cache_Memcache)
            $results = $sysCache->getHandler()->getExtendedStats();

        if($dataCache  && $dataCache instanceof Cache_Memcache)
            $results = array_merge($results , $dataCache->getHandler()->getExtendedStats());

        if(empty($results))
            Response::jsonSuccess(array());

        $resultData = array();
        $count = 0;
        foreach ($results as $k=>$v)
        {
            foreach ($v as $item=>$value)
            {
                $resultData[] = array(
                    'group'=>$k,
                    'title'=>$item,
                    'value'=>$value,
                    'id'=>$count
                );
                $count++;
            }
        }

        Response::jsonSuccess(array_values($resultData));
    }
    /**
     * Reset cache
     */
    public function resetAction()
    {
        $this->_checkCanDelete();

        if(Backend_Cache_Manager::resetAll())
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_RESET_CACHE);
    }
    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] =  '/js/app/system/Cache.js';
        /*
         * Module bootstrap
         */
        if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}
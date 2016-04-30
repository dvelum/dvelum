<?php
/**
 * Background tasks module UI Controller
 */
class Backend_Tasks_Controller extends Backend_Controller
{
    /**
     * @var Bgtask_Manager
     */
    protected $_tManager;

    public function __construct()
    {
        parent::__construct();
        $bgStorage = new Bgtask_Storage_Orm(Model::factory('bgtask') , Model::factory('Bgtask_Signal'));
        $logger = new Bgtask_Log_File('./.log/test'.date('YmdHis').'.txt');
        $this->_tManager = Bgtask_Manager::getInstance();
        $this->_tManager->setStorage($bgStorage);
        $this->_tManager->setLogger($logger);
    }

    public function indexAction()
    {
        $this->_resource->addInlineJs('
        	var canEdit = '.($this->_user->canEdit($this->_module)).';
        	var canDelete = '.($this->_user->canDelete($this->_module)).';
        ');
        $this->_resource->addJs('/js/app/system/Tasks.js', 4);
        $this->_resource->addJs('/js/app/system/crud/'.strtolower($this->_module).'.js', 4);
    }

    /**
     * Get tasks list
     */
    public function listAction()
    {
        $data = $this->_tManager->getList();

        if(!empty($data)){
            $dict =  Dictionary::getInstance('task');
            foreach ($data as $k=>&$v){
                $v['status_code'] = $v['status'];
                if($dict->isValidKey($v['status']))
                    $v['status'] = $dict->getValue($v['status']);

                $finish = strtotime($v['time_finished']);
                if($finish<=0){
                    $finish = time();
                }

                $v['runtime'] = Utils::formatTime($finish - strtotime($v['time_started']));

                $v['memory'] = Utils::formatFileSize($v['memory']);
                $v['memory_peak'] = Utils::formatFileSize($v['memory_peak']);
                if($v['op_total']==0)
                    $v['progress'] = 0;
                else
                    $v['progress'] = number_format(($v['op_finished']) / $v['op_total'] , 2) * 100;
            }unset($v);
        }
        Response::jsonSuccess($data);
    }

    /**
     * Launch test task
     */
    public function testAction()
    {
        $db = Model::getDefaultDbManager()->getDbConnection('default');
        $db->getProfiler()->setEnabled(false);
        $this->_tManager->launch(Bgtask_Manager::LAUNCHER_JSON, 'Task_Test' , array());
    }

    /**
     * Kill task by pid
     */
    public function killAction()
    {
        $this->_checkCanEdit();

        $pid = Request::post('pid', 'integer', 0);
        if(!$pid)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if($this->_tManager->kill($pid))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_EXEC);
    }

    /**
     * Pause task by pid
     */
    public function pauseAction()
    {
        $this->_checkCanEdit();

        $pid = Request::post('pid', 'integer', 0);
        if(!$pid)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if($this->_tManager->signal($pid, Bgtask_Abstract::SIGNAL_SLEEP))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_EXEC);
    }

    /**
     * Resume task by pid
     */
    public function resumeAction()
    {
        $this->_checkCanEdit();

        $pid = Request::post('pid', 'integer', 0);
        if(!$pid)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if($this->_tManager->signal($pid, Bgtask_Abstract::SIGNAL_CONTINUE))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_EXEC);
    }

    /**
     * Stop task by pid
     */
    public function stopAction()
    {
        $this->_checkCanEdit();

        $pid = Request::post('pid', 'integer', 0);
        if(!$pid)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if($this->_tManager->signal($pid, Bgtask_Abstract::SIGNAL_STOP))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_EXEC);
    }

    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] =  '/js/app/system/Tasks.js';
        /*
         * Module bootstrap
         */
        if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';

        return $projectData;
    }
}
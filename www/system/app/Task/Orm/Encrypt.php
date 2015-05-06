<?php
class Task_Orm_Encrypt extends Bgtask_Abstract
{
    protected $buckedSize = 20;
    /**
     * (non-PHPdoc)
     * @see Bgtask_Abstract::getDescription()
     */
    public function getDescription()
    {
        $lang = Lang::lang();
        return $lang->get('ENCRYPT_DATA') . ': ' .  $this->_config['object'];
    }

    public function goBackground()
    {
        ini_set('max_execution_time' , 0);
        ini_set('ignore_user_abort' ,'On');
        session_write_close();

        echo json_encode(array('success'=>true));
        echo ob_get_clean();
        flush();
        if(function_exists('fastcgi_finish_request')){
            fastcgi_finish_request();
        }
        ob_start();
    }
    /**
     * (non-PHPdoc)
     * @see Bgtask_Abstract::run()
     */
    public function run()
    {
        $object = $this->_config['object'];
        $container = $this->_config['session_container'];

        /*
         * Save task ID into session for UI
         */
        $session = Store_Session::getInstance();
        $session->set($container , $this->_pid);
        $this->goBackground();

        $objectConfig = Db_Object_Config::getInstance($object);
        $ivField = $objectConfig->getIvField();
        $primaryKey = $objectConfig->getPrimaryKey();

        $model = Model::factory($object);
        $count = Model::factory($object)->getCount(array($ivField=>null));
        $this->setTotalCount($count);

        if(!$count)
            $this->finish();

        $ignore = array();
        $data = $model->getList(array('limit'=>$this->buckedSize) , array($ivField=>null) , array($primaryKey));

        while(!empty($data))
        {
            $ids = Utils::fetchCol($primaryKey , $data);

            $objectList = Db_Object::factory($object , $ids);
            $count = 0;
            foreach($objectList as $dataObject)
            {
                if(!$dataObject->save()){
                    $ignore[] = $dataObject->getId();
                    $this->log('Cannot encrypt '.$dataObject->getName() .' '.$dataObject->getId());
                }else{
                    $count ++;
                }
            }
            /*
            * Update task status and check for signals
            */
            $this->incrementCompleted($count);
            $this->updateState();
            $this->processSignals();

            if(!empty($ignore)){
                $filters = array(
                  $ivField => null,
                  $primaryKey=> new Db_Select_Filter($primaryKey,$ignore,Db_Select_Filter::NOT_IN)
                );
            }else{
                $filters = array(
                    $ivField => null
                );
            }
            $data = $model->getList(array('limit'=>$this->buckedSize) , $filters , array($primaryKey));
        }
        $this->finish();
    }
}
<?php
class Task_Orm_Decrypt extends Bgtask_Abstract
{
    protected $buckedSize = 20;

    /**
     * (non-PHPdoc)
     * @see Bgtask_Abstract::getDescription()
     */
    public function getDescription()
    {
        $lang = Lang::lang();
        return $lang->get('DECRYPT_DATA') . ': ' . $this->_config['object'];
    }

    public function goBackground()
    {
        ini_set('max_execution_time', 0);
        ini_set('ignore_user_abort', 'On');
        session_write_close();

        echo json_encode(array('success' => true));
        echo ob_get_clean();
        flush();
        if (function_exists('fastcgi_finish_request')) {
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

        if(!$objectConfig->hasEncrypted())
            $this->finish();

        $filter = array(
            $ivField=> new Db_Select_Filter($ivField , false , Db_Select_Filter::NOT_NULL)
        );

        $model = Model::factory($object);
        $count = Model::factory($object)->getCount($filter);

        $this->setTotalCount($count);

        if(!$count)
            $this->finish();

        $data = $model->getList(array('limit'=>$this->buckedSize) , $filter, array($primaryKey));

        $encryptedFields = $objectConfig->getEncryptedFields();

        while(!empty($data))
        {
            $ids = Utils::fetchCol($primaryKey , $data);

            $objectList = Db_Object::factory($object , $ids);
            $count = 0;
            foreach($objectList as $dataObject)
            {
                $data = array();
                foreach($encryptedFields as $name){
                    $data[$name] = $dataObject->get($name);
                    $model->logError($dataObject->getId().' '.$name.': '.$data[$name]);
                }
                $data[$ivField] = null;
                try{
                    $model->getDbConnection()->update($model->table() , $data , $primaryKey.' = '.$dataObject->getId());
                    $count ++;
                }catch (Exception $e){
                    $errorText = 'Cannot decrypt '.$dataObject->getName() .' '.$dataObject->getId().' '.$e->getMessage();
                    $model->logError($errorText);
                    $this->error($errorText);
                }
            }
            /*
            * Update task status and check for signals
            */
            $this->incrementCompleted($count);
            $this->updateState();
            $this->processSignals();

            $data = $model->getList(array('limit'=>$this->buckedSize) , $filter , array($primaryKey));
        }
        $this->finish();
    }

}